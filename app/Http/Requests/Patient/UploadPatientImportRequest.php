<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadPatientImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('patient.import_excel') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
            'campaign_id' => ['nullable', 'integer', Rule::exists('campaigns', 'id')],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
