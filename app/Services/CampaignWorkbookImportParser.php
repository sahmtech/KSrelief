<?php

namespace App\Services;

use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CampaignWorkbookImportParser
{
    /**
     * @return list<string>
     */
    public function sheetNames(string $fullPath): array
    {
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);

        return $reader->listWorksheetNames($fullPath);
    }

    /**
     * @param  list<string>  $sheetNames
     */
    public function isCampaignWorkbook(array $sheetNames): bool
    {
        $hasMain = collect($sheetNames)->contains(
            fn (string $name): bool => (bool) preg_match(config('patient_workbook_import.main_sheet_pattern'), $name)
        );

        $hasDay = collect($sheetNames)->contains(
            fn (string $name): bool => (bool) preg_match(config('patient_workbook_import.day_sheet_pattern'), $name)
        );

        return $hasMain || $hasDay;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parse(string $fullPath): array
    {
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);

        /** @var array<string, array<string, mixed>> $patients */
        $patients = [];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $title = $worksheet->getTitle();

            if (preg_match(config('patient_workbook_import.main_sheet_pattern'), $title)) {
                $this->parseMainSheet($worksheet, $patients);
            } elseif (preg_match(config('patient_workbook_import.day_sheet_pattern'), $title, $matches)) {
                $this->parseDaySheet($worksheet, $patients, (int) $matches[1]);
            }
        }

        return array_values($patients);
    }

    /**
     * @param  array<string, array<string, mixed>>  $patients
     */
    private function parseMainSheet(Worksheet $worksheet, array &$patients): void
    {
        $rows = $worksheet->toArray();
        if (count($rows) < 2) {
            return;
        }

        $headerMap = $this->buildHeaderMap($rows[0], config('patient_workbook_import.main_columns', []));

        for ($i = 1; $i < count($rows); $i++) {
            $mapped = $this->mapRowByHeader($rows[$i], $headerMap);
            $name = $this->cleanName($mapped['patient_name'] ?? null);

            if ($name === null) {
                continue;
            }

            $key = $this->patientKey($name);
            $entry = $this->blankPatientEntry($name);
            $entry = $this->mergePatientEntry($entry, $mapped, 'main');
            $patients[$key] = $this->mergePatientEntry($patients[$key] ?? $entry, $mapped, 'main');
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $patients
     */
    private function parseDaySheet(Worksheet $worksheet, array &$patients, int $dayNumber): void
    {
        $rows = $worksheet->toArray();
        if (count($rows) < 2) {
            return;
        }

        $headerMap = $this->buildHeaderMap($rows[0], config('patient_workbook_import.day_columns', []));

        for ($i = 1; $i < count($rows); $i++) {
            $mapped = $this->mapRowByHeader($rows[$i], $headerMap);
            $name = $this->cleanName($mapped['patient_name'] ?? null);

            if ($name === null) {
                continue;
            }

            $key = $this->patientKey($name);
            $entry = $patients[$key] ?? $this->blankPatientEntry($name);
            $entry['surgery_day_number'] = $dayNumber;
            $entry = $this->mergePatientEntry($entry, $mapped, 'day');
            $patients[$key] = $entry;
        }
    }

    /**
     * @param  list<mixed>  $headerRow
     * @param  array<string, list<string>>  $columnConfig
     * @return array<int, string>
     */
    private function buildHeaderMap(array $headerRow, array $columnConfig): array
    {
        $map = [];

        foreach ($headerRow as $index => $cell) {
            $normalized = $this->normalizeHeader((string) $cell);

            if ($normalized === '') {
                continue;
            }

            foreach ($columnConfig as $fieldKey => $aliases) {
                foreach ($aliases as $alias) {
                    if ($normalized === $this->normalizeHeader($alias)) {
                        $map[$index] = $fieldKey;
                        break 2;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @param  list<mixed>  $row
     * @param  array<int, string>  $headerMap
     * @return array<string, mixed>
     */
    private function mapRowByHeader(array $row, array $headerMap): array
    {
        $data = [];

        foreach ($headerMap as $index => $fieldKey) {
            $value = $row[$index] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $data[$fieldKey] = is_string($value) ? trim($value) : $value;
        }

        return $data;
    }

  /**
     * @return array<string, mixed>
     */
    private function blankPatientEntry(string $name): array
    {
        return [
            'patient_name' => $name,
            'file_number' => null,
            'date_of_birth' => null,
            'gender' => null,
            'contact_number' => null,
            'eligibility_status' => null,
            'admission_status' => null,
            'approval_reason' => null,
            'surgical_side' => null,
            'rank' => null,
            'surgery_day_number' => null,
            'patient_notes' => null,
            'screening_data' => [],
            'medical_records' => [],
            'import_source' => 'campaign_workbook',
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<string, mixed>  $mapped
     * @return array<string, mixed>
     */
    private function mergePatientEntry(array $entry, array $mapped, string $source): array
    {
        foreach (['patient_name', 'file_number', 'contact_number', 'approval_reason', 'patient_notes'] as $field) {
            if (filled($mapped[$field] ?? null)) {
                $entry[$field] = trim((string) $mapped[$field]);
            }
        }

        if (filled($mapped['rank'] ?? null) && is_numeric($mapped['rank'])) {
            $entry['rank'] = (int) $mapped['rank'];
        }

        if (filled($mapped['approval_status'] ?? null)) {
            $entry['eligibility_status'] = $this->mapEligibility((string) $mapped['approval_status']);
        }

        if (filled($mapped['admission_status'] ?? null)) {
            $entry['admission_status'] = $this->mapAdmission((string) $mapped['admission_status']);
        }

        if (filled($mapped['gender'] ?? null)) {
            $entry['gender'] = $this->mapGender((string) $mapped['gender']);
        }

        if (filled($mapped['date_of_birth'] ?? null)) {
            $entry['date_of_birth'] = $this->parseDate($mapped['date_of_birth']);
        } elseif (filled($mapped['current_age'] ?? null) && ! filled($entry['date_of_birth'])) {
            $entry['date_of_birth'] = $this->estimateDobFromAge((string) $mapped['current_age']);
        }

        if (filled($mapped['surgical_side'] ?? null)) {
            $entry['surgical_side'] = $this->mapSide((string) $mapped['surgical_side']);
        }

        foreach (config('patient_workbook_import.screening_field_keys', []) as $screeningKey) {
            if (filled($mapped[$screeningKey] ?? null)) {
                $entry['screening_data'][$screeningKey] = $this->stringValue($mapped[$screeningKey]);
            }
        }

        foreach (config('patient_workbook_import.stage_field_map', []) as $stageCode => $fields) {
            foreach ($fields as $fieldKey) {
                if (filled($mapped[$fieldKey] ?? null)) {
                    $entry['medical_records'][$stageCode][$fieldKey] = $this->stringValue($mapped[$fieldKey]);
                }
            }
        }

        return $entry;
    }

    private function patientKey(string $name): string
    {
        return Str::lower(preg_replace('/\s+/', ' ', trim($name)) ?? $name);
    }

    private function cleanName(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $name = trim((string) $value);

        return $name !== '' ? $name : null;
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/\s+/', ' ', trim($header)) ?? $header;

        return Str::lower($header);
    }

    private function mapEligibility(string $value): string
    {
        $normalized = Str::lower(trim($value));

        if (str_contains($normalized, 'reject') || str_contains($normalized, 'regect')) {
            return 'rejected';
        }

        if (str_contains($normalized, 'wait')) {
            return 'postponed';
        }

        if (str_contains($normalized, 're-assessment') || str_contains($normalized, 'reassessment')) {
            return 'postponed';
        }

        if (str_contains($normalized, 'accept')) {
            return 'accepted';
        }

        return 'postponed';
    }

    private function mapAdmission(string $value): string
    {
        $normalized = Str::lower(trim($value));

        return str_contains($normalized, 'hospital') || $normalized === 'admitted'
            ? 'admitted'
            : 'not_admitted';
    }

    private function mapGender(string $value): ?string
    {
        $normalized = Str::lower(trim($value));

        return match (true) {
            str_starts_with($normalized, 'm') => 'male',
            str_starts_with($normalized, 'f') => 'female',
            default => null,
        };
    }

    private function mapSide(string $value): ?string
    {
        $normalized = Str::lower(trim($value));

        if (str_contains($normalized, 'bil')) {
            return 'bilateral';
        }

        if (str_contains($normalized, 'left') || $normalized === 'l') {
            return 'left';
        }

        if (str_contains($normalized, 'right') || $normalized === 'r' || str_contains($normalized, 'osia')) {
            return 'right';
        }

        return null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return now()->parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function estimateDobFromAge(string $ageText): ?string
    {
        if (! preg_match('/(\d+)\s*years?/i', $ageText, $yearsMatch)) {
            return null;
        }

        $years = (int) $yearsMatch[1];
        $months = 0;

        if (preg_match('/(\d+)\s*months?/i', $ageText, $monthsMatch)) {
            $months = (int) $monthsMatch[1];
        }

        return now()->subYears($years)->subMonths($months)->format('Y-m-d');
    }

    private function stringValue(mixed $value): string
    {
        if (is_numeric($value) && ! is_string($value)) {
            return (string) $value;
        }

        return trim((string) $value);
    }
}
