<?php

namespace App\Http\Requests\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('medical_record.update');
    }

    public function rules(): array
    {
        return [
            'stage_id'    => ['nullable', 'integer', 'exists:patient_stages,id'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
            'record_date' => ['required', 'date'],
            'notes'       => ['nullable', 'string', 'max:2000'],
            'field_*'     => ['nullable'],
            'fields'      => ['nullable', 'array'],
            'admission_attachments' => ['nullable', 'array'],
            'admission_attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }
}
