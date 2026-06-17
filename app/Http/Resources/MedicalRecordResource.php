<?php

namespace App\Http\Resources;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MedicalRecord
 */
class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'patient_id'  => $this->patient_id,
            'record_date' => $this->record_date?->toDateString(),
            'stage'       => $this->whenLoaded('stage', fn () => [
                'id'    => $this->stage->id,
                'name'  => $this->stage->name,
                'code'  => $this->stage->code,
                'color' => $this->stage->color,
            ]),
            'specialty'   => $this->whenLoaded('specialty', fn () => $this->specialty ? [
                'id'   => $this->specialty->id,
                'name' => $this->specialty->name,
            ] : null),
            'fields'      => $this->fields_json,
            'notes'       => $this->notes,
            'submitted_by' => $this->whenLoaded('submitter', fn () => [
                'id'   => $this->submitter->id,
                'name' => $this->submitter->name,
            ]),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
