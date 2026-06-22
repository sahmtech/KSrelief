<?php

namespace App\Models;

use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Enums\PatientRecordStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'surgery_day_number',
        'rank',
        'patient_name',
        'photo',
        'file_number',
        'date_of_birth',
        'age_years',
        'age_months',
        'gender',
        'height_cm',
        'weight_kg',
        'contact_number',
        'eligibility_status_id',
        'approval_reason',
        'current_stage_id',
        'admission_status',
        'surgical_side',
        'notes',
        'screening_data',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age_years' => 'integer',
            'age_months' => 'integer',
            'gender' => Gender::class,
            'height_cm' => 'decimal:1',
            'weight_kg' => 'decimal:1',
            'admission_status' => AdmissionStatus::class,
            'status' => PatientRecordStatus::class,
            'screening_data' => 'array',
            'surgery_day_number' => 'integer',
            'rank' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function eligibilityStatus(): BelongsTo
    {
        return $this->belongsTo(PatientEligibilityStatus::class, 'eligibility_status_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(PatientStage::class, 'current_stage_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PatientAttachment::class);
    }

    /**
     * Future: Medical Records module.
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Future: Stage History module.
     */
    public function stageHistories(): HasMany
    {
        return $this->hasMany(PatientStageHistory::class);
    }

    public function transportationPassengers(): HasMany
    {
        return $this->hasMany(TransportationTripPassenger::class);
    }

    public function activityParticipants(): HasMany
    {
        return $this->hasMany(ActivityParticipant::class);
    }

    public function initials(): string
    {
        $name = trim($this->patient_name);

        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', $name) ?: [];

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function hasPhoto(): bool
    {
        return filled($this->photo);
    }

    public function photoUrl(): ?string
    {
        if (! $this->hasPhoto()) {
            return null;
        }

        return '/storage/'.str_replace('\\', '/', $this->photo);
    }

    protected static function booted(): void
    {
        static::deleting(function (Patient $patient): void {
            if ($patient->photo) {
                Storage::disk('public')->delete($patient->photo);
            }
        });
    }

    public function ageLabel(): string
    {
        if ($this->age_years === null) {
            return '—';
        }

        if ($this->age_months > 0 && $this->age_years < 2) {
            return __('patients.age.months', ['months' => $this->age_months]);
        }

        return __('patients.age.years', ['years' => $this->age_years]);
    }

    public function heightLabel(): string
    {
        if ($this->height_cm === null) {
            return '—';
        }

        return __('patients.measurements.height_value', ['value' => rtrim(rtrim(number_format((float) $this->height_cm, 1, '.', ''), '0'), '.')]);
    }

    public function weightLabel(): string
    {
        if ($this->weight_kg === null) {
            return '—';
        }

        return __('patients.measurements.weight_value', ['value' => rtrim(rtrim(number_format((float) $this->weight_kg, 1, '.', ''), '0'), '.')]);
    }

    public function admissionLabel(): string
    {
        return $this->admission_status?->label() ?? '—';
    }

    public function admissionBadgeClass(): string
    {
        return $this->admission_status?->badgeClass() ?? 'badge-status--inactive';
    }

    public function recordStatusLabel(): string
    {
        return $this->status?->label() ?? '—';
    }

    public function recordStatusBadgeClass(): string
    {
        return $this->status?->badgeClass() ?? 'badge-status--inactive';
    }

    public function screening(string $key, mixed $default = null): mixed
    {
        return $this->screening_data[$key] ?? $default;
    }

    public function surgeryDayLabel(): string
    {
        if (! $this->surgery_day_number) {
            return '—';
        }

        return __('patients.fields.surgery_day_number_value', ['day' => $this->surgery_day_number]);
    }

    public function surgicalSideLabel(): string
    {
        if (! filled($this->surgical_side)) {
            return '—';
        }

        return __('workflow.sides.'.$this->surgical_side);
    }

    /**
     * @param  Builder<Patient>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('patient_name', 'like', "%{$term}%")
                ->orWhere('file_number', 'like', "%{$term}%")
                ->orWhere('contact_number', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<Patient>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['campaign_id'] ?? null, fn (Builder $q, int $id) => $q->where('campaign_id', $id))
            ->when($filters['surgery_day_number'] ?? null, fn (Builder $q, int $day) => $q->where('surgery_day_number', $day))
            ->when($filters['eligibility_status_id'] ?? null, fn (Builder $q, int $id) => $q->where('eligibility_status_id', $id))
            ->when($filters['current_stage_id'] ?? null, fn (Builder $q, int $id) => $q->where('current_stage_id', $id))
            ->when($filters['admission_status'] ?? null, fn (Builder $q, string $status) => $q->where('admission_status', $status))
            ->when($filters['gender'] ?? null, fn (Builder $q, string $gender) => $q->where('gender', $gender))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['created_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['created_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));
    }
}
