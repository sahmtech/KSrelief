<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activity.update');
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['sometimes', 'required', 'integer', 'exists:campaigns,id'],
            'activity_type_id' => ['sometimes', 'required', 'integer', 'exists:activity_types,id'],
            'patient_stage_id' => ['nullable', 'integer', 'exists:patient_stages,id'],
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'activity_date' => ['sometimes', 'required', 'date'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
