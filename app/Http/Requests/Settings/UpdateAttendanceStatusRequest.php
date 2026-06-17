<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var AttendanceStatus $attendanceStatus */
        $attendanceStatus = $this->route('attendance_status');

        return $this->user()?->can('update', $attendanceStatus) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var AttendanceStatus $attendanceStatus */
        $attendanceStatus = $this->route('attendance_status');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('attendance_statuses', 'code')->ignore($attendanceStatus->id)],
            'color' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
