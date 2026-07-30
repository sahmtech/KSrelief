<?php

namespace App\Services;

use App\Models\PreOperationFormDefault;
use App\Models\User;
use App\Support\PreOperationFieldSupport;
use App\Support\ScreeningFieldSupport;

class PreOperationFormDefaultService
{
    /** @var list<string> */
    private const FIELD_KEYS = [
        'physician_assessment',
        'imaging_findings',
        'audiology_decision',
        'speech_assessment',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function getForUser(User $user): ?array
    {
        $record = PreOperationFormDefault::query()->where('user_id', $user->id)->first();

        if (! $record) {
            return null;
        }

        return $this->normalizePayload($record->defaults_json ?? []);
    }

    public function hasForUser(User $user): bool
    {
        return PreOperationFormDefault::query()->where('user_id', $user->id)->exists();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function templateForForm(User $user): ?array
    {
        $saved = $this->getForUser($user);

        if (! is_array($saved)) {
            return null;
        }

        return $this->prefillForForm($saved);
    }

    /**
     * @param  array<string, mixed>  $fieldsJson
     */
    public function saveForUser(User $user, array $fieldsJson): PreOperationFormDefault
    {
        $payload = [];

        foreach (self::FIELD_KEYS as $key) {
            if (array_key_exists($key, $fieldsJson)) {
                $payload[$key] = $fieldsJson[$key];
            }
        }

        return PreOperationFormDefault::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['defaults_json' => $this->normalizePayload($payload)]
        );
    }

    /**
     * @param  array<string, mixed>  $requestData
     */
    public function saveFromRequestInput(User $user, array $requestData): PreOperationFormDefault
    {
        return $this->saveForUser($user, [
            'physician_assessment' => $requestData['field_physician_assessment'] ?? null,
            'imaging_findings' => $requestData['field_imaging_findings'] ?? null,
            'audiology_decision' => $requestData['field_audiology_decision'] ?? null,
            'speech_assessment' => $requestData['field_speech_assessment'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function prefillForForm(?array $savedDefaults = null): array
    {
        $base = PreOperationFieldSupport::defaultFormPayload();

        if (! is_array($savedDefaults)) {
            return $base;
        }

        return [
            'physician_assessment' => PreOperationFieldSupport::resolvePhysicianAssessmentForForm(
                $savedDefaults['physician_assessment'] ?? null
            ),
            'imaging_findings' => ScreeningFieldSupport::resolveImagingFindingsForForm(
                $savedDefaults['imaging_findings'] ?? null
            ),
            'audiology_decision' => PreOperationFieldSupport::resolveAudiologyDecisionForForm(
                $savedDefaults['audiology_decision'] ?? null
            ),
            'speech_assessment' => array_merge(
                PreOperationFieldSupport::defaultSpeechAssessment(),
                PreOperationFieldSupport::normalizeSpeechAssessment($savedDefaults['speech_assessment'] ?? null)
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'physician_assessment' => PreOperationFieldSupport::normalizePhysicianAssessment(
                $payload['physician_assessment'] ?? null
            ),
            'imaging_findings' => ScreeningFieldSupport::normalizeImagingFindings(
                $payload['imaging_findings'] ?? null
            ),
            'audiology_decision' => PreOperationFieldSupport::normalizeAudiologyDecision(
                $payload['audiology_decision'] ?? null
            ),
            'speech_assessment' => PreOperationFieldSupport::normalizeSpeechAssessment(
                $payload['speech_assessment'] ?? null
            ),
        ];
    }
}
