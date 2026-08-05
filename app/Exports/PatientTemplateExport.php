<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PatientTemplateExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @return list<string>
     */
    public static function columnHeadings(): array
    {
        return config('patient_import.template_columns', []);
    }

    /**
     * @return list<string>
     */
    public static function requiredColumnHeadings(): array
    {
        return config('patient_import.required_columns', []);
    }

    /**
     * @return list<list<string|null>>
     */
    public function array(): array
    {
        return [
            [
                'Ahmed Al-Zahrani',
                '2015-06-20',
                'male',
                '120',
                '32.5',
                '+966501234567',
            ],
            [
                'Sara Al-Otaibi',
                '',
                '',
                '',
                '',
                '',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return self::columnHeadings();
    }

    public function title(): string
    {
        return 'Patients';
    }
}
