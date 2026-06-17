<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\ActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ActivityType::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('activity_types', 'code')],
            'color' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
