<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MemberTemplateExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @return list<list<string|null>>
     */
    public function array(): array
    {
        return [
            [
                'Ahmed',
                'Al-Harbi',
                '+966501234567',
                'ahmed@example.com',
                'male',
                '1990-05-15',
                'Saudi Arabia',
                'doctor',
                'Cardiology',
                'active',
                'Sample row — delete before import',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'first_name',
            'last_name',
            'mobile',
            'email',
            'gender',
            'date_of_birth',
            'nationality',
            'role_code',
            'specialty_name',
            'status',
            'notes',
        ];
    }

    public function title(): string
    {
        return 'Members';
    }
}
