<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePatientImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('patient.import_approve') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
