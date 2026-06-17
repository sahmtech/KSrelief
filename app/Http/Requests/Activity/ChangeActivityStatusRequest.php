<?php

namespace App\Http\Requests\Activity;

use App\Enums\ActivityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeActivityStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activity.change_status');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ActivityStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
