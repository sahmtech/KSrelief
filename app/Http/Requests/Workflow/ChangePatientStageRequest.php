<?php

namespace App\Http\Requests\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class ChangePatientStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('stage.change');
    }

    public function rules(): array
    {
        return [
            'to_stage_id' => ['required', 'integer', 'exists:patient_stages,id'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_stage_id.required' => __('workflow.validation.stage_required'),
            'to_stage_id.exists'   => __('workflow.validation.stage_not_found'),
        ];
    }
}
