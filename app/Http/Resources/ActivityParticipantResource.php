<?php

namespace App\Http\Resources;

use App\Models\ActivityParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ActivityParticipant */
class ActivityParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activity_id' => $this->activity_id,
            'participant_type' => $this->participant_type?->value,
            'participant_name' => $this->participantName(),
            'member_id' => $this->member_id,
            'patient_id' => $this->patient_id,
            'attendance_status' => $this->attendance_status?->value,
            'attendance_status_label' => $this->attendanceStatusLabel(),
            'notes' => $this->notes,
            'member' => $this->whenLoaded('member', fn () => [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient->id,
                'patient_name' => $this->patient->patient_name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
