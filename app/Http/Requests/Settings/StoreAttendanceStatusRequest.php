<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AttendanceStatus::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('attendance_statuses', 'code')],
            'color' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
