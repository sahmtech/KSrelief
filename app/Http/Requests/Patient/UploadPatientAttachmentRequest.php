<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use App\Models\PatientAttachment;
use Illuminate\Foundation\Http\FormRequest;

class UploadPatientAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $patient = $this->route('patient');

        return $patient instanceof Patient
            && ($this->user()?->can('update', $patient) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
