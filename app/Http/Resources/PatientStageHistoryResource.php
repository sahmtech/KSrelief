<?php

namespace App\Http\Resources;

use App\Models\PatientStageHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PatientStageHistory
 */
class PatientStageHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'patient_id' => $this->patient_id,
            'from_stage' => $this->whenLoaded('fromStage', fn () => $this->fromStage ? [
                'id'    => $this->fromStage->id,
                'name'  => $this->fromStage->name,
                'code'  => $this->fromStage->code,
                'color' => $this->fromStage->color,
            ] : null),
            'to_stage'   => $this->whenLoaded('toStage', fn () => [
                'id'    => $this->toStage->id,
                'name'  => $this->toStage->name,
                'code'  => $this->toStage->code,
                'color' => $this->toStage->color,
            ]),
            'changed_by' => $this->whenLoaded('changedBy', fn () => [
                'id'   => $this->changedBy->id,
                'name' => $this->changedBy->name,
            ]),
            'changed_at' => $this->changed_at?->toIso8601String(),
            'notes'      => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
