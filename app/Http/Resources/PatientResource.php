<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Patient
 */
class PatientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'campaign' => $this->whenLoaded('campaign', fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
                'code' => $this->campaign->code,
            ]),
            'patient_name' => $this->patient_name,
            'photo_url' => $this->photoUrl(),
            'file_number' => $this->file_number,
            'file_number_color' => $this->when(
                isset($this->file_number_accent_color),
                fn () => $this->file_number_accent_color ?? $this->fileNumberAccentColor()
            ),
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age_years' => $this->age_years,
            'age_months' => $this->age_months,
            'age_label' => $this->ageLabel(),
            'gender' => $this->gender?->value,
            'gender_label' => $this->gender?->label(),
            'height_cm' => $this->height_cm,
            'weight_kg' => $this->weight_kg,
            'contact_number' => $this->contact_number,
            'surgery_day_number' => $this->surgery_day_number,
            'surgery_day_label' => $this->surgeryDayLabel(),
            'rank' => $this->rank,
            'surgical_side' => $this->surgical_side,
            'approval_reason' => $this->approval_reason,
            'screening_data' => $this->screening_data ?? [],
            'eligibility_status' => $this->whenLoaded('eligibilityStatus', fn () => [
                'id' => $this->eligibilityStatus->id,
                'name' => $this->eligibilityStatus->name,
                'code' => $this->eligibilityStatus->code,
                'color' => $this->eligibilityStatus->color,
            ]),
            'current_stage' => $this->whenLoaded('currentStage', fn () => $this->currentStage ? [
                'id' => $this->currentStage->id,
                'name' => $this->currentStage->displayName(),
                'code' => $this->currentStage->code,
                'color' => $this->currentStage->color,
            ] : null),
            'admission_status' => $this->admission_status?->value,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'attachments' => PatientAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
