<?php

namespace App\Services;

use App\Models\PostOperationFormDefault;
use App\Models\User;
use App\Support\PostOperationFieldSupport;

class PostOperationFormDefaultService
{
    /** @var list<string> */
    private const FIELD_KEYS = [
        'physician_assessment',
        'clinical_aud',
        'counselling',
        'post_op_notes',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function getForUser(User $user): ?array
    {
        $record = PostOperationFormDefault::query()->where('user_id', $user->id)->first();

        if (! $record) {
            return null;
        }

        return $this->normalizePayload($record->defaults_json ?? []);
    }

    public function hasForUser(User $user): bool
    {
        return PostOperationFormDefault::query()->where('user_id', $user->id)->exists();
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
    public function saveForUser(User $user, array $fieldsJson): PostOperationFormDefault
    {
        $payload = [];

        foreach (self::FIELD_KEYS as $key) {
            if (array_key_exists($key, $fieldsJson)) {
                $payload[$key] = $fieldsJson[$key];
            }
        }

        return PostOperationFormDefault::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['defaults_json' => $this->normalizePayload($payload)]
        );
    }

    /**
     * @param  array<string, mixed>  $requestData
     */
    public function saveFromRequestInput(User $user, array $requestData): PostOperationFormDefault
    {
        return $this->saveForUser($user, [
            'physician_assessment' => $requestData['field_physician_assessment'] ?? null,
            'clinical_aud' => $requestData['field_clinical_aud'] ?? null,
            'counselling' => $requestData['field_counselling'] ?? null,
            'post_op_notes' => $requestData['field_post_op_notes'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function prefillForForm(?array $savedDefaults = null): array
    {
        $base = PostOperationFieldSupport::defaultFormPayload();

        if (! is_array($savedDefaults)) {
            return $base;
        }

        return [
            'physician_assessment' => PostOperationFieldSupport::resolvePhysicianAssessmentForForm(
                $savedDefaults['physician_assessment'] ?? null
            ),
            'clinical_aud' => PostOperationFieldSupport::resolveClinicalAudForForm(
                $savedDefaults['clinical_aud'] ?? null
            ),
            'counselling' => PostOperationFieldSupport::normalizeCounselling(
                $savedDefaults['counselling'] ?? null
            ),
            'post_op_notes' => PostOperationFieldSupport::resolveNotesForForm(
                $savedDefaults['post_op_notes'] ?? null
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
            'physician_assessment' => PostOperationFieldSupport::normalizePhysicianAssessment(
                $payload['physician_assessment'] ?? null
            ),
            'clinical_aud' => PostOperationFieldSupport::normalizeClinicalAud(
                $payload['clinical_aud'] ?? null
            ),
            'counselling' => PostOperationFieldSupport::normalizeCounselling(
                $payload['counselling'] ?? null
            ),
            'post_op_notes' => PostOperationFieldSupport::normalizeNotes(
                $payload['post_op_notes'] ?? null
            ),
        ];
    }
}
