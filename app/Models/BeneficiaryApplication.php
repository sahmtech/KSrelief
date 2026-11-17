<?php

namespace App\Models;

use App\Enums\BeneficiaryApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BeneficiaryApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'status',
        'extra_type',
        'full_name',
        'national_number',
        'mobile',
        'email',
        'gender',
        'birth_date',
        'country_code',
        'application_payload',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'accepted_patient_id',
        'accepted_campaign_id',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status' => BeneficiaryApplicationStatus::class,
            'extra_type' => 'integer',
            'gender' => 'integer',
            'birth_date' => 'date',
            'application_payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $application): void {
            if (! filled($application->uuid)) {
                $application->uuid = (string) Str::uuid();
            }
        });
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(BeneficiaryApplicationAttachment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function acceptedPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'accepted_patient_id');
    }

    public function acceptedCampaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'accepted_campaign_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BeneficiaryApplicationStatus::Pending);
    }

    public function isPending(): bool
    {
        return $this->status === BeneficiaryApplicationStatus::Pending;
    }

    public function payload(string $key, mixed $default = null): mixed
    {
        return data_get($this->application_payload, $key, $default);
    }

    public function extraTypeLabel(): string
    {
        $key = config("beneficiary_registration.extra_types.{$this->extra_type}.label");

        return $key ? __($key) : (string) $this->extra_type;
    }

    public function genderLabel(): string
    {
        $mapped = config("beneficiary_registration.gender.{$this->gender}");

        return match ($mapped) {
            'male' => __('beneficiary_registration.options.male'),
            'female' => __('beneficiary_registration.options.female'),
            default => (string) $this->gender,
        };
    }
}
