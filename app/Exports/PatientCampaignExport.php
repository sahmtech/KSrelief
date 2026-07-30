<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PatientCampaignExport implements WithMultipleSheets
{
    /**
     * @param  list<PatientExportSheet>  $sheets
     */
    public function __construct(
        private readonly array $sheets
    ) {}

    /**
     * @return list<PatientExportSheet>
     */
    public function sheets(): array
    {
        return $this->sheets;
    }
}
