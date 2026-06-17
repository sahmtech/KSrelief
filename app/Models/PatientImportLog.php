<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientImportLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'batch_id',
        'row_number',
        'patient_name',
        'file_number',
        'validation_errors',
        'is_valid',
        'is_duplicate',
        'duplicate_reason',
        'raw_data',
        'patient_id',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'validation_errors' => 'array',
            'is_valid' => 'boolean',
            'is_duplicate' => 'boolean',
            'raw_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PatientImportBatch::class, 'batch_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function isImportable(): bool
    {
        return $this->is_valid && ! $this->is_duplicate && $this->patient_id === null;
    }

    public function rowStatusLabel(): string
    {
        if ($this->is_duplicate) {
            return __('patients.import.row_status.duplicate');
        }

        if ($this->patient_id !== null) {
            return __('patients.import.row_status.imported');
        }

        if ($this->is_valid) {
            return __('patients.import.row_status.valid');
        }

        return __('patients.import.row_status.invalid');
    }

    public function rowStatusBadgeClass(): string
    {
        if ($this->is_duplicate) {
            return 'badge-status--warning';
        }

        if ($this->patient_id !== null) {
            return 'badge-status--active';
        }

        if ($this->is_valid) {
            return 'badge-status--success';
        }

        return 'badge-status--danger';
    }
}
