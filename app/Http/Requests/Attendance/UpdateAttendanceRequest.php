<?php

namespace App\Http\Requests\Attendance;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.update');
    }

    public function rules(): array
    {
        /** @var Attendance $attendance */
        $attendance = $this->route('attendance');

        return [
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'member_id' => [
                'required',
                'integer',
                'exists:members,id',
                Rule::exists('campaign_member', 'member_id')->where(
                    fn ($q) => $q->where('campaign_id', $this->input('campaign_id'))
                ),
            ],
            'attendance_date' => ['required', 'date'],
            'shift_number' => ['nullable', 'integer', 'min:1', 'max:10'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'attendance_status_id' => ['required', 'integer', 'exists:attendance_statuses,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('shift_number')) {
            $this->merge(['shift_number' => 1]);
        }
    }

    public function withValidator($validator): void
    {
        /** @var Attendance $attendance */
        $attendance = $this->route('attendance');

        $validator->after(function ($validator) use ($attendance): void {
            $exists = Attendance::query()
                ->where('id', '!=', $attendance->id)
                ->where('campaign_id', $this->input('campaign_id'))
                ->where('member_id', $this->input('member_id'))
                ->whereDate('attendance_date', $this->input('attendance_date'))
                ->where('shift_number', $this->input('shift_number', 1))
                ->exists();

            if ($exists) {
                $validator->errors()->add('member_id', __('attendance.errors.duplicate_record'));
            }
        });
    }

    public function messages(): array
    {
        return [
            'member_id.exists' => __('attendance.errors.member_not_assigned'),
        ];
    }
}
