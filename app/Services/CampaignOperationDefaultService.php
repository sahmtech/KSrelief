<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignOperationDefault;
use App\Models\ImplantCompany;
use App\Models\ImplantElectrodeType;
use App\Models\InsertionApproach;
use App\Support\CampaignOperationDefaultCatalog;
use App\Support\OperationFieldSupport;

class CampaignOperationDefaultService
{
    /** @var list<string> */
    private const FIELD_KEYS = [
        'implant_company_id',
        'electrode_type_id',
        'insertion_approach_id',
        'insertion_depth',
        'audio_test',
        'intra_op_findings',
    ];

    public function hasForCampaign(?Campaign $campaign): bool
    {
        if (! $campaign) {
            return false;
        }

        return $this->supportedCompanies()->isNotEmpty();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function defaultsForForm(?Campaign $campaign = null): array
    {
        $companies = $this->supportedCompanies();
        $saved = [];

        if ($campaign) {
            $saved = CampaignOperationDefault::query()
                ->where('campaign_id', $campaign->id)
                ->get()
                ->keyBy('implant_company_id')
                ->map(fn (CampaignOperationDefault $row): array => $this->normalizePayload($row->defaults_json ?? []))
                ->all();
        }

        $result = [];

        foreach ($companies as $company) {
            $companyId = (int) $company->id;
            $payload = $saved[$companyId] ?? CampaignOperationDefaultCatalog::resolvePayloadForCompanyCode((string) $company->code);
            $payload['implant_company_id'] = (string) $companyId;
            $result[$companyId] = $this->templateForForm($payload);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getForCampaignCompany(Campaign $campaign, int $implantCompanyId): ?array
    {
        $record = CampaignOperationDefault::query()
            ->where('campaign_id', $campaign->id)
            ->where('implant_company_id', $implantCompanyId)
            ->first();

        if ($record) {
            return $this->normalizePayload($record->defaults_json ?? []);
        }

        $company = ImplantCompany::query()->find($implantCompanyId);

        if (! $company) {
            return null;
        }

        return $this->normalizePayload(
            CampaignOperationDefaultCatalog::resolvePayloadForCompanyCode((string) $company->code)
        );
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $inputByCompany
     */
    public function syncForCampaign(Campaign $campaign, array $inputByCompany): void
    {
        $companyIds = $this->supportedCompanies()->pluck('id')->map(fn ($id): int => (int) $id)->all();

        foreach ($companyIds as $companyId) {
            $input = $inputByCompany[$companyId] ?? $inputByCompany[(string) $companyId] ?? null;

            if (! is_array($input)) {
                continue;
            }

            $input['implant_company_id'] = (string) $companyId;

            CampaignOperationDefault::query()->updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'implant_company_id' => $companyId,
                ],
                ['defaults_json' => $this->normalizePayload($input)]
            );
        }
    }

    public function seedSystemDefaults(Campaign $campaign): void
    {
        $payload = [];

        foreach ($this->supportedCompanies() as $company) {
            $payload[(int) $company->id] = CampaignOperationDefaultCatalog::resolvePayloadForCompanyCode((string) $company->code);
        }

        $this->syncForCampaign($campaign, $payload);
    }

    public function ensureDefaultsForCampaign(Campaign $campaign): void
    {
        $existingCompanyIds = CampaignOperationDefault::query()
            ->where('campaign_id', $campaign->id)
            ->pluck('implant_company_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $missingCompanies = $this->supportedCompanies()
            ->reject(fn (ImplantCompany $company): bool => in_array((int) $company->id, $existingCompanyIds, true));

        if ($missingCompanies->isEmpty()) {
            return;
        }

        $payload = [];

        foreach ($missingCompanies as $company) {
            $payload[(int) $company->id] = CampaignOperationDefaultCatalog::resolvePayloadForCompanyCode((string) $company->code);
        }

        $this->syncForCampaign($campaign, $payload);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function summariesForShow(Campaign $campaign): array
    {
        $this->ensureDefaultsForCampaign($campaign);

        $summaries = [];

        foreach ($this->supportedCompanies() as $company) {
            $defaults = $this->getForCampaignCompany($campaign, (int) $company->id);

            if ($defaults === null) {
                continue;
            }

            $electrode = filled($defaults['electrode_type_id'] ?? null)
                ? ImplantElectrodeType::query()->find($defaults['electrode_type_id'])
                : null;
            $approach = filled($defaults['insertion_approach_id'] ?? null)
                ? InsertionApproach::query()->find($defaults['insertion_approach_id'])
                : null;

            $summaries[] = [
                'company' => $company,
                'electrode_name' => $electrode?->name ?? '—',
                'approach_name' => $approach?->name ?? '—',
                'insertion_depth' => $defaults['insertion_depth'] ?? null,
                'intra_op_findings' => $defaults['intra_op_findings'] ?? null,
                'audio_test' => $defaults['audio_test'] ?? null,
            ];
        }

        return $summaries;
    }

    /**
     * @return \Illuminate\Support\Collection<int, ImplantCompany>
     */
    public function supportedCompanies()
    {
        return ImplantCompany::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>|null  $savedDefaults
     * @return array<string, mixed>
     */
    public function templateForForm(?array $savedDefaults = null): array
    {
        if (! is_array($savedDefaults)) {
            return OperationFieldSupport::defaultTemplatePayload();
        }

        return [
            'implant_company_id' => $savedDefaults['implant_company_id'] ?? null,
            'electrode_type_id' => $savedDefaults['electrode_type_id'] ?? null,
            'insertion_approach_id' => $savedDefaults['insertion_approach_id'] ?? null,
            'insertion_depth' => OperationFieldSupport::resolveInsertionDepthForForm($savedDefaults['insertion_depth'] ?? null),
            'audio_test' => OperationFieldSupport::resolveAudioTestForForm($savedDefaults['audio_test'] ?? null),
            'intra_op_findings' => (string) ($savedDefaults['intra_op_findings'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'implant_company_id' => filled($payload['implant_company_id'] ?? null) ? (string) $payload['implant_company_id'] : null,
            'electrode_type_id' => filled($payload['electrode_type_id'] ?? null) ? (string) $payload['electrode_type_id'] : null,
            'insertion_approach_id' => filled($payload['insertion_approach_id'] ?? null) ? (string) $payload['insertion_approach_id'] : null,
            'insertion_depth' => OperationFieldSupport::normalizeInsertionDepth($payload['insertion_depth'] ?? null),
            'audio_test' => OperationFieldSupport::normalizeAudioTest($payload['audio_test'] ?? null),
            'intra_op_findings' => filled($payload['intra_op_findings'] ?? null) ? (string) $payload['intra_op_findings'] : null,
        ];
    }
}
