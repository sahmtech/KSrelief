<?php

namespace App\Http\Requests\Workflow;

use App\Http\Requests\Workflow\Concerns\MergesMedicalRecordFieldInputs;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    use MergesMedicalRecordFieldInputs;

    public function authorize(): bool
    {
        return $this->user()->can('medical_record.create');
    }

    public function rules(): array
    {
        return [
            'stage_id'    => ['required', 'integer', 'exists:patient_stages,id'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
            'record_date' => ['required', 'date'],
            'notes'       => ['nullable', 'string', 'max:2000'],
            'fields'      => ['nullable', 'array'],
            'admission_attachments' => ['nullable', 'array'],
            'admission_attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }
}
