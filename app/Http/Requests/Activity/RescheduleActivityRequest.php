<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activity.update');
    }

    public function rules(): array
    {
        return [
            'activity_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ];
    }
}
