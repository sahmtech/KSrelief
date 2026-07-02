<?php

namespace App\Support;

use App\Models\ImplantCompany;
use App\Models\ImplantElectrodeType;
use App\Models\InsertionApproach;

final class CampaignOperationDefaultCatalog
{
    /** @var list<string> */
    public const SUPPORTED_COMPANY_CODES = ['cochlear', 'medel'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function blueprintByCompanyCode(): array
    {
        return [
            'cochlear' => [
                'electrode_name' => 'CI522 Slim Straight',
                'insertion_approach_codes' => ['round_window', 'round'],
                'insertion_depth' => 'full_insertion',
                'intra_op_findings' => 'uneventful',
                'audio_test' => [
                    'Impedance' => 'normal',
                    'E cap' => 'present all Electrode',
                ],
            ],
            'medel' => [
                'electrode_name' => 'Flex 26',
                'insertion_approach_codes' => ['round_window', 'round'],
                'insertion_depth' => 'full_insertion',
                'intra_op_findings' => 'uneventful',
                'audio_test' => [
                    'Impedance' => 'normal',
                    'E cap' => 'present all Electrode',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolvePayloadForCompanyCode(string $companyCode): array
    {
        $blueprint = self::blueprintByCompanyCode()[$companyCode] ?? null;

        if ($blueprint === null) {
            return OperationFieldSupport::defaultTemplatePayload();
        }

        $company = ImplantCompany::query()->where('code', $companyCode)->first();

        if (! $company) {
            return OperationFieldSupport::defaultTemplatePayload();
        }

        $electrodeId = ImplantElectrodeType::query()
            ->where('implant_company_id', $company->id)
            ->where('name', $blueprint['electrode_name'])
            ->value('id');

        if (! $electrodeId) {
            $electrodeId = ImplantElectrodeType::query()
                ->where('implant_company_id', $company->id)
                ->where('name', 'like', '%'.str_replace('CI522 ', '', $blueprint['electrode_name']).'%')
                ->value('id');
        }

        $approachId = self::resolveInsertionApproachId($blueprint['insertion_approach_codes']);

        $metrics = ClinicalCompositeFields::defaultMetrics(OperationFieldSupport::AUDIO_TEST_KEYS);
        foreach ($metrics as &$row) {
            $key = $row['key'] ?? '';
            if (array_key_exists($key, $blueprint['audio_test'])) {
                $row['value'] = $blueprint['audio_test'][$key];
            }
        }
        unset($row);

        return [
            'implant_company_id' => (string) $company->id,
            'electrode_type_id' => $electrodeId ? (string) $electrodeId : null,
            'insertion_approach_id' => $approachId ? (string) $approachId : null,
            'insertion_depth' => [
                'selection' => $blueprint['insertion_depth'],
                'note' => '',
            ],
            'intra_op_findings' => $blueprint['intra_op_findings'],
            'audio_test' => ['metrics' => $metrics],
        ];
    }

    /**
     * @param  list<string>  $codes
     */
    private static function resolveInsertionApproachId(array $codes): ?int
    {
        foreach ($codes as $code) {
            $id = InsertionApproach::query()->where('code', $code)->value('id');
            if ($id) {
                return (int) $id;
            }
        }

        return InsertionApproach::query()
            ->where('name', 'like', '%Round%Window%')
            ->value('id');
    }
}
