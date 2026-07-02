<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientStageHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'from_stage_id',
        'to_stage_id',
        'changed_by',
        'changed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(PatientStage::class, 'from_stage_id')->withTrashed();
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(PatientStage::class, 'to_stage_id')->withTrashed();
    }

    public function fromStageLabel(): string
    {
        return self::resolveStageLabel($this->fromStage, $this->from_stage_id);
    }

    public function toStageLabel(): string
    {
        return self::resolveStageLabel($this->toStage, $this->to_stage_id);
    }

    public function fromStageColor(): string
    {
        return $this->fromStage?->color ?? '#6B7280';
    }

    public function toStageColor(): string
    {
        return $this->toStage?->color ?? '#9CA3AF';
    }

    private static function resolveStageLabel(?PatientStage $stage, ?int $stageId): string
    {
        if ($stage) {
            return $stage->displayName();
        }

        if ($stageId) {
            return __('workflow.history.deleted_stage', ['id' => $stageId]);
        }

        return '—';
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
