<?php

namespace App\Models;

use App\Enums\PatientImportBatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientImportBatch extends Model
{
    protected $fillable = [
        'campaign_id',
        'file_name',
        'original_file_name',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'duplicate_rows',
        'imported_count',
        'status',
        'imported_by',
        'approved_by',
        'approved_at',
        'notes',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'valid_rows' => 'integer',
            'invalid_rows' => 'integer',
            'duplicate_rows' => 'integer',
            'imported_count' => 'integer',
            'status' => PatientImportBatchStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PatientImportLog::class, 'batch_id');
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '—';
    }

    public function statusBadgeClass(): string
    {
        return $this->status?->badgeClass() ?? 'badge-status--inactive';
    }

    public function storagePath(): string
    {
        return 'patient-imports/'.$this->id.'/'.$this->file_name;
    }
}
