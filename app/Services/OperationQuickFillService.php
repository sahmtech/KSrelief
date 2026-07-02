<?php

namespace App\Services;

use App\Enums\SettingStatus;
use App\Models\Campaign;
use App\Models\ImplantCompany;
use App\Models\InsertionApproach;
use App\Models\OperationQuickFillPreset;
use App\Models\User;
use App\Support\OperationFieldSupport;

class OperationQuickFillService
{
    /** Legacy combination/stored presets — kept in code but hidden from UI. */
    private const INCLUDE_LEGACY_PRESETS = false;

    /** @var list<string> */
    private const COMBINATION_BASE_KEYS = [
        'surgeon',
        'time_in_surgery',
        'time_out_surgery',
        'audio_test',
        'operation_notes',
    ];

    /** @var list<string> */
    private const CARD_SUMMARY_KEYS = [
        'electrode',
        'insertion_approach',
        'insertion_depth',
        'intra_op_findings',
    ];

    public function __construct(
        private readonly OperationFormDefaultService $defaultService,
        private readonly CampaignOperationDefaultService $campaignDefaultService,
        private readonly LookupService $lookupService,
    ) {}

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     source: string,
     *     summary: array<string, string>,
     *     payload: array<string, mixed>
     * }>
     */
    public function presetsForCompany(int $companyId, ?User $user = null, ?Campaign $campaign = null): array
    {
        $company = ImplantCompany::query()
            ->where('status', SettingStatus::Active->value)
            ->find($companyId);

        if (! $company) {
            return [];
        }

        $presets = [];

        $userPreset = $this->userDefaultPreset($company, $user);
        if ($userPreset !== null) {
            $presets[] = $userPreset;
        }

        $campaignPreset = $this->campaignDefaultPreset($company, $campaign, $user);
        if ($campaignPreset !== null) {
            $presets[] = $campaignPreset;
        }

        if (self::INCLUDE_LEGACY_PRESETS) {
            foreach ($this->storedPresets($company) as $preset) {
                $presets[] = $preset;
            }

            foreach ($this->combinationPresets($company, $user) as $preset) {
                $presets[] = $preset;
            }
        }

        return $presets;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function storedPresets(ImplantCompany $company): array
    {
        return OperationQuickFillPreset::query()
            ->where('implant_company_id', $company->id)
            ->where('status', SettingStatus::Active->value)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (OperationQuickFillPreset $preset) use ($company): array {
                $payload = $this->normalizePayload(
                    array_merge(
                        ['implant_company_id' => (string) $company->id],
                        $preset->preset_json ?? []
                    )
                );

                return $this->formatPreset(
                    id: 'stored-'.$preset->id,
                    name: $preset->name,
                    source: 'stored',
                    payload: $payload,
                    company: $company,
                );
            })
            ->all();
    }

    /**
     * All list-field combinations: electrode × approach × insertion depth × intra-op findings.
     *
     * @return list<array<string, mixed>>
     */
    private function combinationPresets(ImplantCompany $company, ?User $user): array
    {
        $basePayload = $this->basePayloadForCombination($user);
        $electrodes = $this->lookupService->getImplantElectrodeTypes($company->id);

        if ($electrodes->isEmpty()) {
            return [];
        }

        $approachSlots = $this->approachSlots();
        $depthSlots = $this->depthSlots();
        $findingsSlots = $this->findingsSlots();

        $presets = [];

        foreach ($electrodes as $electrode) {
            foreach ($approachSlots as $approachId => $approachName) {
                foreach ($depthSlots as $depthCode => $depthLabel) {
                    foreach ($findingsSlots as $findingsCode => $findingsLabel) {
                        $payload = array_merge($basePayload, [
                            'implant_company_id' => (string) $company->id,
                            'electrode_type_id' => (string) $electrode->id,
                            'insertion_approach_id' => filled($approachId) ? (string) $approachId : null,
                            'insertion_depth' => filled($depthCode)
                                ? [
                                    'selection' => (string) $depthCode,
                                    'note' => '',
                                ]
                                : OperationFieldSupport::defaultInsertionDepth(),
                            'intra_op_findings' => filled($findingsCode) ? (string) $findingsCode : null,
                        ]);

                        $presets[] = $this->formatPreset(
                            id: sprintf(
                                'combo-%d-%s-%s-%s',
                                $electrode->id,
                                filled($approachId) ? $approachId : '0',
                                filled($depthCode) ? $depthCode : 'none',
                                filled($findingsCode) ? $findingsCode : 'none',
                            ),
                            name: $electrode->name,
                            source: 'combination',
                            payload: $payload,
                            company: $company,
                        );
                    }
                }
            }
        }

        return $presets;
    }

    /**
     * @return array<int|string, string|null> id => label
     */
    private function approachSlots(): array
    {
        $approaches = $this->lookupService->getInsertionApproaches();

        if ($approaches->isEmpty()) {
            return ['' => null];
        }

        return $approaches->mapWithKeys(fn ($approach): array => [
            (int) $approach->id => (string) $approach->name,
        ])->all();
    }

    /**
     * @return array<string, string|null> code => label
     */
    private function depthSlots(): array
    {
        $options = OperationFieldSupport::insertionDepthOptions();

        if ($options === []) {
            return ['' => null];
        }

        return $options;
    }

    /**
     * @return array<string, string|null> code => label
     */
    private function findingsSlots(): array
    {
        $options = OperationFieldSupport::intraOpFindingsOptions();

        if ($options === []) {
            return ['' => null];
        }

        return $options;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function userDefaultPreset(ImplantCompany $company, ?User $user): ?array
    {
        if (! $user || ! $this->defaultService->hasForUser($user)) {
            return null;
        }

        $saved = $this->defaultService->getForUser($user);
        $payload = $this->normalizePayload(
            $this->defaultService->templateForForm($saved)
        );

        if ((string) ($payload['implant_company_id'] ?? '') !== (string) $company->id) {
            return null;
        }

        return $this->formatPreset(
            id: 'user-default',
            name: __('workflow.operation.quick_fill.my_template'),
            source: 'user_default',
            payload: $payload,
            company: $company,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function campaignDefaultPreset(ImplantCompany $company, ?Campaign $campaign, ?User $user): ?array
    {
        if (! $campaign) {
            return null;
        }

        $defaults = $this->campaignDefaultService->getForCampaignCompany($campaign, (int) $company->id);

        if ($defaults === null) {
            return null;
        }

        $payload = $this->normalizePayload(
            array_merge(
                $this->basePayloadForCombination($user),
                $this->campaignDefaultService->templateForForm($defaults),
                ['implant_company_id' => (string) $company->id],
            )
        );

        return $this->formatPreset(
            id: 'campaign-default',
            name: __('workflow.operation.quick_fill.campaign_default'),
            source: 'campaign_default',
            payload: $payload,
            company: $company,
        );
    }

    /**
     * Non-list fields merged from the user's saved template (surgeon, times, audio, notes).
     *
     * @return array<string, mixed>
     */
    private function basePayloadForCombination(?User $user): array
    {
        $payload = OperationFieldSupport::defaultTemplatePayload();

        if (! $user || ! $this->defaultService->hasForUser($user)) {
            return $payload;
        }

        $userTemplate = $this->normalizePayload(
            $this->defaultService->templateForForm(
                $this->defaultService->getForUser($user)
            )
        );

        foreach (self::COMBINATION_BASE_KEYS as $key) {
            if ($this->hasTemplateValue($key, $userTemplate[$key] ?? null)) {
                $payload[$key] = $userTemplate[$key];
            }
        }

        return $payload;
    }

    private function hasTemplateValue(string $key, mixed $value): bool
    {
        if ($key === 'insertion_depth') {
            $data = OperationFieldSupport::normalizeInsertionDepth($value);

            return filled($data['selection']) || filled($data['note']);
        }

        if ($key === 'audio_test') {
            return OperationFieldSupport::hasAudioTestContent($value);
        }

        if ($key === 'operation_notes') {
            return filled(trim((string) $value));
        }

        return filled($value);
    }

    private function approachLabel(mixed $approachId): ?string
    {
        if (! filled($approachId)) {
            return null;
        }

        return InsertionApproach::query()->find((int) $approachId)?->name;
    }

    private function insertionDepthSummaryLabel(mixed $insertionDepth): ?string
    {
        $data = OperationFieldSupport::normalizeInsertionDepth($insertionDepth);

        if (! filled($data['selection'])) {
            return null;
        }

        $label = OperationFieldSupport::insertionDepthOptions()[$data['selection']] ?? $data['selection'];

        if (filled($data['note'])) {
            return "{$label}: {$data['note']}";
        }

        return $label;
    }

    private function intraOpFindingsSummaryLabel(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $label = OperationFieldSupport::presentIntraOpFindings($value);

        return $label !== '—' ? $label : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        unset($payload['_apply_labels']);

        return [
            'surgeon' => filled($payload['surgeon'] ?? null) ? (string) $payload['surgeon'] : null,
            'implant_company_id' => filled($payload['implant_company_id'] ?? null) ? (string) $payload['implant_company_id'] : null,
            'electrode_type_id' => filled($payload['electrode_type_id'] ?? null) ? (string) $payload['electrode_type_id'] : null,
            'insertion_approach_id' => filled($payload['insertion_approach_id'] ?? null) ? (string) $payload['insertion_approach_id'] : null,
            'insertion_depth' => OperationFieldSupport::normalizeInsertionDepth($payload['insertion_depth'] ?? null),
            'time_in_surgery' => filled($payload['time_in_surgery'] ?? null) ? (string) $payload['time_in_surgery'] : null,
            'time_out_surgery' => filled($payload['time_out_surgery'] ?? null) ? (string) $payload['time_out_surgery'] : null,
            'audio_test' => OperationFieldSupport::normalizeAudioTest($payload['audio_test'] ?? null),
            'intra_op_findings' => filled($payload['intra_op_findings'] ?? null) ? (string) $payload['intra_op_findings'] : null,
            'operation_notes' => trim((string) ($payload['operation_notes'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function formatPreset(
        string $id,
        string $name,
        string $source,
        array $payload,
        ImplantCompany $company,
    ): array {
        $electrodes = $this->lookupService->getImplantElectrodeTypes($company->id);
        $electrodeName = $electrodes->firstWhere('id', (int) ($payload['electrode_type_id'] ?? 0))?->name;

        $notSet = __('workflow.operation.quick_fill.not_set');

        $summaryValues = [
            'electrode' => $electrodeName,
            'insertion_approach' => $this->approachLabel($payload['insertion_approach_id'] ?? null),
            'insertion_depth' => $this->insertionDepthSummaryLabel($payload['insertion_depth'] ?? null),
            'intra_op_findings' => $this->intraOpFindingsSummaryLabel($payload['intra_op_findings'] ?? null),
        ];

        $summary = [];
        foreach (self::CARD_SUMMARY_KEYS as $key) {
            $summary[$key] = filled($summaryValues[$key] ?? null)
                ? (string) $summaryValues[$key]
                : $notSet;
        }

        return [
            'id' => $id,
            'name' => $name,
            'source' => $source,
            'company' => [
                'id' => (string) $company->id,
                'name' => $company->name,
                'color' => $company->color,
            ],
            'summary' => $summary,
            'payload' => OperationFieldSupport::payloadWithApplyLabels($payload),
        ];
    }
}
