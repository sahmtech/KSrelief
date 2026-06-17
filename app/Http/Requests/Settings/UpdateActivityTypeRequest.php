<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\ActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ActivityType $activityType */
        $activityType = $this->route('activity_type');

        return $this->user()?->can('update', $activityType) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ActivityType $activityType */
        $activityType = $this->route('activity_type');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('activity_types', 'code')->ignore($activityType->id)],
            'color' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
