<?php

namespace App\Services;

use App\Models\OperationFormDefault;
use App\Models\User;
use App\Support\OperationFieldSupport;

class OperationFormDefaultService
{
    /** @var list<string> */
    private const FIELD_KEYS = [
        'surgeon',
        'implant_company_id',
        'electrode_type_id',
        'insertion_approach_id',
        'insertion_depth',
        'time_in_surgery',
        'time_out_surgery',
        'audio_test',
        'intra_op_findings',
        'operation_notes',
    ];

    public function hasForUser(User $user): bool
    {
        return OperationFormDefault::query()->where('user_id', $user->id)->exists();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getForUser(User $user): ?array
    {
        $record = OperationFormDefault::query()->where('user_id', $user->id)->first();

        if (! $record) {
            return null;
        }

        return $this->normalizePayload($record->defaults_json ?? []);
    }

    /**
     * @param  array<string, mixed>  $requestData
     */
    public function saveFromRequestInput(User $user, array $requestData): OperationFormDefault
    {
        $payload = [];

        foreach (self::FIELD_KEYS as $key) {
            $inputKey = 'field_'.$key;
            if (array_key_exists($inputKey, $requestData)) {
                $payload[$key] = $requestData[$inputKey];
            }
        }

        return OperationFormDefault::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['defaults_json' => $this->normalizePayload($payload)]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function templateForForm(?array $savedDefaults = null): array
    {
        if (! is_array($savedDefaults)) {
            return OperationFieldSupport::defaultTemplatePayload();
        }

        return [
            'surgeon' => $savedDefaults['surgeon'] ?? null,
            'implant_company_id' => $savedDefaults['implant_company_id'] ?? null,
            'electrode_type_id' => $savedDefaults['electrode_type_id'] ?? null,
            'insertion_approach_id' => $savedDefaults['insertion_approach_id'] ?? null,
            'insertion_depth' => OperationFieldSupport::resolveInsertionDepthForForm($savedDefaults['insertion_depth'] ?? null),
            'time_in_surgery' => (string) ($savedDefaults['time_in_surgery'] ?? ''),
            'time_out_surgery' => (string) ($savedDefaults['time_out_surgery'] ?? ''),
            'audio_test' => OperationFieldSupport::resolveAudioTestForForm($savedDefaults['audio_test'] ?? null),
            'intra_op_findings' => (string) ($savedDefaults['intra_op_findings'] ?? ''),
            'operation_notes' => (string) ($savedDefaults['operation_notes'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
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
}
