<?php

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attendance
 */
class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'member_id' => $this->member_id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'shift_number' => $this->shift_number,
            'check_in' => $this->checkInLabel(),
            'check_out' => $this->checkOutLabel(),
            'worked_minutes' => $this->worked_minutes,
            'worked_hours' => $this->workedHoursLabel(),
            'notes' => $this->notes,
            'campaign' => $this->whenLoaded('campaign', fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
            ]),
            'member' => $this->whenLoaded('member', fn () => [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'role' => $this->member->memberRole?->name,
                'specialty' => $this->member->specialty?->name,
            ]),
            'attendance_status' => $this->whenLoaded('attendanceStatus', fn () => [
                'id' => $this->attendanceStatus->id,
                'name' => $this->attendanceStatus->name,
                'code' => $this->attendanceStatus->code,
                'color' => $this->attendanceStatus->color,
            ]),
            'recorded_by' => $this->whenLoaded('recorder', fn () => [
                'id' => $this->recorder->id,
                'name' => $this->recorder->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
