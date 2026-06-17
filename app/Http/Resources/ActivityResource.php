<?php

namespace App\Http\Resources;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Activity */
class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'activity_type_id' => $this->activity_type_id,
            'patient_stage_id' => $this->patient_stage_id,
            'title' => $this->title,
            'description' => $this->description,
            'activity_date' => $this->activity_date?->toDateString(),
            'start_time' => $this->startTimeLabel(),
            'end_time' => $this->endTimeLabel(),
            'location' => $this->location,
            'status' => $this->status?->value,
            'status_label' => $this->statusLabel(),
            'max_participants' => $this->max_participants,
            'participants_count' => $this->participantsCount(),
            'color' => $this->calendarColor(),
            'campaign' => $this->whenLoaded('campaign', fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
            ]),
            'activity_type' => $this->whenLoaded('activityType', fn () => [
                'id' => $this->activityType->id,
                'name' => $this->activityType->name,
                'code' => $this->activityType->code,
                'color' => $this->activityType->color,
            ]),
            'participants' => ActivityParticipantResource::collection($this->whenLoaded('participants')),
            'created_by' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
