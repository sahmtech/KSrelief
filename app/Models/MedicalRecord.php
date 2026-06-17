<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'stage_id',
        'specialty_id',
        'record_date',
        'fields_json',
        'notes',
        'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'fields_json' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PatientStage::class, 'stage_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get a specific field value from fields_json.
     */
    public function field(string $key, mixed $default = null): mixed
    {
        return $this->fields_json[$key] ?? $default;
    }
}
