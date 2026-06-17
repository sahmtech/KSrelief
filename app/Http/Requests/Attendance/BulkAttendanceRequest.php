<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.create');
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'attendance_date' => ['required', 'date'],
            'shift_number' => ['nullable', 'integer', 'min:1', 'max:10'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.member_id' => ['required', 'integer', 'exists:members,id'],
            'rows.*.attendance_status_id' => ['required', 'integer', 'exists:attendance_statuses,id'],
            'rows.*.check_in' => ['nullable', 'date_format:H:i'],
            'rows.*.check_out' => ['nullable', 'date_format:H:i'],
            'rows.*.notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('shift_number')) {
            $this->merge(['shift_number' => 1]);
        }
    }
}
