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
                'name'  => $this->fromStage->displayName(),
                'code'  => $this->fromStage->code,
                'color' => $this->fromStage->color,
            ] : ($this->from_stage_id ? [
                'id'    => $this->from_stage_id,
                'name'  => $this->fromStageLabel(),
                'code'  => null,
                'color' => $this->fromStageColor(),
            ] : null)),
            'to_stage'   => $this->whenLoaded('toStage', fn () => $this->toStage ? [
                'id'    => $this->toStage->id,
                'name'  => $this->toStage->displayName(),
                'code'  => $this->toStage->code,
                'color' => $this->toStage->color,
            ] : [
                'id'    => $this->to_stage_id,
                'name'  => $this->toStageLabel(),
                'code'  => null,
                'color' => $this->toStageColor(),
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
