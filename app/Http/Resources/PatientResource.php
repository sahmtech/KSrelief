<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for Doctor Mobile App (endpoints not implemented yet).
 *
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
            'patient_name' => $this->patient_name,
            'photo_url' => $this->photoUrl(),
            'file_number' => $this->file_number,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age_years' => $this->age_years,
            'age_months' => $this->age_months,
            'gender' => $this->gender?->value,
            'contact_number' => $this->contact_number,
            'eligibility_status' => $this->whenLoaded('eligibilityStatus', fn () => [
                'id' => $this->eligibilityStatus->id,
                'name' => $this->eligibilityStatus->name,
                'code' => $this->eligibilityStatus->code,
            ]),
            'current_stage' => $this->whenLoaded('currentStage', fn () => $this->currentStage ? [
                'id' => $this->currentStage->id,
                'name' => $this->currentStage->name,
                'code' => $this->currentStage->code,
            ] : null),
            'admission_status' => $this->admission_status?->value,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
