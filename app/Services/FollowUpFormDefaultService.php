<?php

namespace App\Services;

use App\Models\FollowUpFormDefault;
use App\Models\User;
use App\Support\FollowUpFieldSupport;

class FollowUpFormDefaultService
{
    /** @var list<string> */
    private const FIELD_KEYS = [
        'clinical_assessment',
        'audiology_assessment',
        'speech_assessment',
        'follow_up_notes',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function getForUser(User $user): ?array
    {
        $record = FollowUpFormDefault::query()->where('user_id', $user->id)->first();

        if (! $record) {
            return null;
        }

        return $this->normalizePayload($record->defaults_json ?? []);
    }

    public function hasForUser(User $user): bool
    {
        return FollowUpFormDefault::query()->where('user_id', $user->id)->exists();
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
    public function saveForUser(User $user, array $fieldsJson): FollowUpFormDefault
    {
        $payload = [];

        foreach (self::FIELD_KEYS as $key) {
            if (array_key_exists($key, $fieldsJson)) {
                $payload[$key] = $fieldsJson[$key];
            }
        }

        return FollowUpFormDefault::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['defaults_json' => $this->normalizePayload($payload)]
        );
    }

    /**
     * @param  array<string, mixed>  $requestData
     */
    public function saveFromRequestInput(User $user, array $requestData): FollowUpFormDefault
    {
        return $this->saveForUser($user, [
            'clinical_assessment' => $requestData['field_clinical_assessment'] ?? null,
            'audiology_assessment' => $requestData['field_audiology_assessment'] ?? null,
            'speech_assessment' => $requestData['field_speech_assessment'] ?? null,
            'follow_up_notes' => $requestData['field_follow_up_notes'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function prefillForForm(?array $savedDefaults = null): array
    {
        $base = FollowUpFieldSupport::defaultFormPayload();

        if (! is_array($savedDefaults)) {
            return $base;
        }

        return [
            'clinical_assessment' => FollowUpFieldSupport::resolveClinicalAssessmentForForm(
                $savedDefaults['clinical_assessment'] ?? null
            ),
            'audiology_assessment' => FollowUpFieldSupport::resolveAudiologyAssessmentForForm(
                $savedDefaults['audiology_assessment'] ?? null
            ),
            'speech_assessment' => array_merge(
                FollowUpFieldSupport::defaultSpeechAssessment(),
                FollowUpFieldSupport::normalizeSpeechAssessment($savedDefaults['speech_assessment'] ?? null)
            ),
            'follow_up_notes' => FollowUpFieldSupport::resolveNotesForForm(
                $savedDefaults['follow_up_notes'] ?? null
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
            'clinical_assessment' => FollowUpFieldSupport::normalizeClinicalAssessment(
                $payload['clinical_assessment'] ?? null
            ),
            'audiology_assessment' => FollowUpFieldSupport::normalizeAudiologyAssessment(
                $payload['audiology_assessment'] ?? null
            ),
            'speech_assessment' => FollowUpFieldSupport::normalizeSpeechAssessment(
                $payload['speech_assessment'] ?? null
            ),
            'follow_up_notes' => FollowUpFieldSupport::normalizeNotes(
                $payload['follow_up_notes'] ?? null
            ),
        ];
    }
}
