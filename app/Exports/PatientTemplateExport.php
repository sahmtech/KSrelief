<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PatientTemplateExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        return [
            [
                'Ahmed Al-Zahrani',
                '',
                '2015-06-20',
                'male',
                '120',
                '32.5',
                '+966501234567',
                'accepted',
                'not_admitted',
                'admission',
                '1',
                '1',
                'right',
                '',
                'Pediatric cardiac case',
            ],
            [
                'Sara Al-Otaibi',
                '',
                '2018-11-05',
                'female',
                '105',
                '28',
                '',
                'accepted',
                'not_admitted',
                '',
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
        return [
            'patient_name',
            'file_number',
            'date_of_birth',
            'gender',
            'height_cm',
            'weight_kg',
            'contact_number',
            'eligibility_status',
            'admission_status',
            'stage',
            'surgery_day_number',
            'rank',
            'surgical_side',
            'approval_reason',
            'patient_notes',
        ];
    }

    public function title(): string
    {
        return 'Patients';
    }
}
