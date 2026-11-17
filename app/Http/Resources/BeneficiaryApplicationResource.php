<?php

namespace App\Http\Resources;

use App\Models\BeneficiaryApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BeneficiaryApplication */
class BeneficiaryApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => [
                'code' => $this->status?->value,
                'label' => $this->status?->label(),
            ],
            'extra_type' => [
                'id' => $this->extra_type,
                'label' => $this->extraTypeLabel(),
            ],
            'full_name' => $this->full_name,
            'national_number' => $this->national_number,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'gender' => [
                'id' => $this->gender,
                'label' => $this->genderLabel(),
            ],
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'country_code' => $this->country_code,
            'country_label' => config('beneficiary_registration.countries.'.$this->country_code),
            'application_payload' => $this->when(
                $this->relationLoaded('attachments') || $request->routeIs('*.beneficiary-applications.show'),
                $this->application_payload
            ),
            'rejection_reason' => $this->rejection_reason,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewed_by' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ] : null),
            'accepted_patient_id' => $this->accepted_patient_id,
            'accepted_campaign' => $this->whenLoaded('acceptedCampaign', fn () => $this->acceptedCampaign ? [
                'id' => $this->acceptedCampaign->id,
                'name' => $this->acceptedCampaign->name,
            ] : null),
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'field_key' => $attachment->field_key,
                    'original_name' => $attachment->original_name,
                    'mime' => $attachment->mime,
                    'size' => $attachment->size,
                    'download_url' => route('api.v1.beneficiary-applications.attachments.download', [
                        'beneficiary_application' => $this->id,
                        'attachment' => $attachment->id,
                    ]),
                ])->values();
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
