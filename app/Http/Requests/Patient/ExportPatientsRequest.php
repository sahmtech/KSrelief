<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use App\Services\PatientExportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportPatientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('export', Patient::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $exportableCodes = app(PatientExportService::class)
            ->exportableStages()
            ->pluck('code')
            ->all();

        return [
            'campaign_id' => ['required', 'integer', Rule::exists('campaigns', 'id')],
            'include_all_stages' => ['nullable', 'boolean'],
            'stages' => ['nullable', 'array'],
            'stages.*' => ['string', Rule::in($exportableCodes)],
        ];
    }

    /**
     * @return list<string>
     */
    public function selectedStageCodes(): array
    {
        if ($this->boolean('include_all_stages')) {
            return ['all'];
        }

        return array_values(array_unique($this->input('stages', [])));
    }
}
