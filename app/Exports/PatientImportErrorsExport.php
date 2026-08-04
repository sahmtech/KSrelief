<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PatientImportErrorsExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param  list<list<mixed>>  $rows
     */
    public function __construct(
        private readonly array $rows
    ) {}

    /**
     * @return list<list<mixed>>
     */
    public function array(): array
    {
        return $this->rows;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'row_number',
            'patient_name',
            'date_of_birth',
            'gender',
            'height_cm',
            'weight_kg',
            'contact_number',
            'errors',
            'duplicate_flag',
        ];
    }

    public function title(): string
    {
        return 'Import Errors';
    }
}
