<?php

namespace App\Http\Resources;

use App\Models\PatientImportBatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientImportBatch */
class PatientImportBatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'campaign' => $this->whenLoaded('campaign', fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
                'code' => $this->campaign->code,
            ]),
            'original_file_name' => $this->original_file_name,
            'status' => $this->status?->value,
            'status_label' => $this->statusLabel(),
            'total_rows' => $this->total_rows,
            'valid_rows' => $this->valid_rows,
            'invalid_rows' => $this->invalid_rows,
            'duplicate_rows' => $this->duplicate_rows,
            'imported_count' => $this->imported_count,
            'failure_reason' => $this->failure_reason,
            'notes' => $this->notes,
            'imported_by' => $this->whenLoaded('importer', fn () => [
                'id' => $this->importer->id,
                'name' => $this->importer->name,
            ]),
            'approved_by' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'permissions' => $user instanceof User ? [
                'can_approve' => $user->can('patient.import_approve') && $this->status?->isApprovable(),
                'can_download_errors' => $user->can('patient.import_history')
                    && in_array($this->status?->value, ['review', 'completed', 'failed'], true),
            ] : [],
        ];
    }
}
