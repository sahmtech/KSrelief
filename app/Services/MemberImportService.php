<?php

namespace App\Services;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\MemberRole;
use App\Models\Specialty;
use App\Models\User;
use App\Support\MemberImportResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MemberImportService
{
    public const AUDIT_IMPORTED = 'member.imported';

    public function __construct(
        private readonly MemberService $memberService
    ) {}

    public function import(UploadedFile $file, User $user): MemberImportResult
    {
        $sheets = Excel::toArray(new \stdClass, $file);
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return new MemberImportResult(errors: [
                ['row' => 0, 'message' => __('members.import.messages.empty_file')],
            ]);
        }

        $header = $this->normalizeHeaderRow($rows[0]);
        $required = ['first_name', 'last_name', 'mobile', 'role_code'];

        foreach ($required as $column) {
            if (! in_array($column, $header, true)) {
                return new MemberImportResult(errors: [
                    ['row' => 1, 'message' => __('members.import.messages.missing_column', ['column' => $column])],
                ]);
            }
        }

        $roleMap = MemberRole::query()->active()->pluck('id', 'code');
        $specialtyMap = Specialty::query()->active()->get()->mapWithKeys(
            fn (Specialty $specialty): array => [
                Str::lower($specialty->name) => $specialty->id,
                Str::lower((string) $specialty->code) => $specialty->id,
            ]
        );

        $result = new MemberImportResult;
        $seenMobiles = [];
        $seenEmails = [];

        for ($index = 1; $index < count($rows); $index++) {
            $rowNumber = $index + 1;
            $raw = $rows[$index];
            $data = $this->mapRow($header, $raw);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $specialtyId = null;

            if (filled($data['specialty_name'] ?? null)) {
                $specialtyId = $specialtyMap->get($data['specialty_name']);

                if ($specialtyId === null) {
                    $result->failed++;
                    $result->errors[] = [
                        'row' => $rowNumber,
                        'message' => __('members.import.messages.invalid_specialty', ['name' => $data['specialty_name']]),
                    ];

                    continue;
                }
            }

            $validationError = $this->validateRow($data, $rowNumber, $seenMobiles, $seenEmails, $roleMap);

            if ($validationError !== null) {
                $result->failed++;
                $result->errors[] = ['row' => $rowNumber, 'message' => $validationError];

                continue;
            }

            if (Member::query()->where('mobile', $data['mobile'])->exists()) {
                $result->skipped++;
                $result->errors[] = [
                    'row' => $rowNumber,
                    'message' => __('members.import.messages.duplicate_mobile', ['mobile' => $data['mobile']]),
                ];

                continue;
            }

            if (filled($data['email']) && Member::query()->where('email', $data['email'])->exists()) {
                $result->skipped++;
                $result->errors[] = [
                    'row' => $rowNumber,
                    'message' => __('members.import.messages.duplicate_email', ['email' => $data['email']]),
                ];

                continue;
            }

            try {
                DB::transaction(function () use ($data, $roleMap, $specialtyId, $user): void {
                    $this->memberService->createMember([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'mobile' => $data['mobile'],
                        'email' => $data['email'] ?: null,
                        'gender' => $data['gender'] ?: null,
                        'date_of_birth' => $data['date_of_birth'] ?: null,
                        'nationality' => $data['nationality'] ?: null,
                        'member_role_id' => $roleMap[$data['role_code']],
                        'specialty_id' => $specialtyId,
                        'status' => $data['status'] ?: MemberStatus::Active->value,
                        'notes' => $data['notes'] ?: null,
                    ], $user);
                });

                $result->success++;
                $seenMobiles[] = $data['mobile'];

                if (filled($data['email'])) {
                    $seenEmails[] = $data['email'];
                }
            } catch (\Throwable $e) {
                $result->failed++;
                $result->errors[] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($result->success === 0 && $result->failed === 0 && $result->skipped === 0) {
            $result->errors[] = ['row' => 0, 'message' => __('members.import.messages.no_data_rows')];
        }

        return $result;
    }

    /**
     * @param  list<mixed>  $headerRow
     * @return list<string>
     */
    private function normalizeHeaderRow(array $headerRow): array
    {
        return array_map(function (mixed $cell): string {
            return Str::snake(Str::lower(trim((string) $cell)));
        }, $headerRow);
    }

    /**
     * @param  list<string>  $header
     * @param  list<mixed>  $row
     * @return array<string, mixed>
     */
    private function mapRow(array $header, array $row): array
    {
        $data = [];

        foreach ($header as $index => $column) {
            if ($column === '') {
                continue;
            }

            $value = $row[$index] ?? null;
            $data[$column] = is_string($value) ? trim($value) : $value;
        }

        if (isset($data['date_of_birth'])) {
            $data['date_of_birth'] = $this->parseDate($data['date_of_birth']);
        }

        if (isset($data['gender']) && is_string($data['gender'])) {
            $data['gender'] = Str::lower($data['gender']);
        }

        if (isset($data['role_code']) && is_string($data['role_code'])) {
            $data['role_code'] = Str::lower($data['role_code']);
        }

        if (isset($data['status']) && is_string($data['status'])) {
            $data['status'] = Str::lower($data['status']);
        }

        if (isset($data['specialty_name']) && is_string($data['specialty_name'])) {
            $data['specialty_name'] = Str::lower(trim($data['specialty_name']));
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        $meaningful = collect($data)
            ->only(['first_name', 'last_name', 'mobile', 'email', 'role_code'])
            ->filter(fn ($value) => filled($value));

        return $meaningful->isEmpty();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $seenMobiles
     * @param  list<string>  $seenEmails
     * @param  \Illuminate\Support\Collection<string, int>  $roleMap
     */
    private function validateRow(
        array $data,
        int $rowNumber,
        array $seenMobiles,
        array $seenEmails,
        $roleMap
    ): ?string {
        if (! filled($data['first_name'] ?? null)) {
            return __('members.import.messages.required', ['field' => 'first_name', 'row' => $rowNumber]);
        }

        if (! filled($data['last_name'] ?? null)) {
            return __('members.import.messages.required', ['field' => 'last_name', 'row' => $rowNumber]);
        }

        if (! filled($data['mobile'] ?? null)) {
            return __('members.import.messages.required', ['field' => 'mobile', 'row' => $rowNumber]);
        }

        if (! filled($data['role_code'] ?? null)) {
            return __('members.import.messages.required', ['field' => 'role_code', 'row' => $rowNumber]);
        }

        if (in_array($data['mobile'], $seenMobiles, true)) {
            return __('members.import.messages.duplicate_in_file', ['field' => 'mobile']);
        }

        if (filled($data['email'] ?? null) && in_array($data['email'], $seenEmails, true)) {
            return __('members.import.messages.duplicate_in_file', ['field' => 'email']);
        }

        if (! $roleMap->has($data['role_code'])) {
            return __('members.import.messages.invalid_role', ['code' => $data['role_code']]);
        }

        if (filled($data['email'] ?? null) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return __('members.import.messages.invalid_email');
        }

        if (filled($data['gender'] ?? null) && ! in_array($data['gender'], Gender::values(), true)) {
            return __('members.import.messages.invalid_gender');
        }

        if (filled($data['status'] ?? null) && ! in_array($data['status'], MemberStatus::values(), true)) {
            return __('members.import.messages.invalid_status');
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
}
