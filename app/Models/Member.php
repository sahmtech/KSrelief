<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'mobile',
        'email',
        'gender',
        'date_of_birth',
        'age',
        'nationality',
        'member_role_id',
        'specialty_id',
        'user_id',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age' => 'integer',
            'gender' => Gender::class,
            'status' => MemberStatus::class,
        ];
    }

    public function memberRole(): BelongsTo
    {
        return $this->belongsTo(MemberRole::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function campaignAssignments(): HasMany
    {
        return $this->hasMany(CampaignMember::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_member')
            ->withPivot(['assigned_role', 'assigned_from', 'assigned_to', 'notes', 'created_by', 'created_at'])
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function transportationPassengers(): HasMany
    {
        return $this->hasMany(TransportationTripPassenger::class);
    }

    public function activityParticipants(): HasMany
    {
        return $this->hasMany(ActivityParticipant::class);
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '—';
    }

    public function statusBadgeClass(): string
    {
        return $this->status?->badgeClass() ?? 'badge-status--inactive';
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->full_name)) ?: [];

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($this->full_name, 0, 2));
    }

    /**
     * @param  Builder<Member>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('full_name', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('nationality', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<Member>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['member_role_id'] ?? null, fn (Builder $q, int $roleId) => $q->where('member_role_id', $roleId))
            ->when($filters['specialty_id'] ?? null, fn (Builder $q, int $specialtyId) => $q->where('specialty_id', $specialtyId))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status));
    }

    /**
     * @param  Builder<Member>  $query
     */
    public function scopeSortable(Builder $query, ?string $sort, ?string $direction = 'desc'): Builder
    {
        $allowed = [
            'full_name',
            'mobile',
            'email',
            'status',
            'created_at',
        ];

        $sort = in_array($sort, $allowed, true) ? $sort : 'created_at';
        $direction = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'member_role') {
            return $query
                ->leftJoin('member_roles', 'members.member_role_id', '=', 'member_roles.id')
                ->orderBy('member_roles.name', $direction)
                ->select('members.*');
        }

        if ($sort === 'specialty') {
            return $query
                ->leftJoin('specialties', 'members.specialty_id', '=', 'specialties.id')
                ->orderBy('specialties.name', $direction)
                ->select('members.*');
        }

        return $query->orderBy($sort, $direction);
    }

    public function computeFullName(string $firstName, string $lastName): string
    {
        return trim($firstName.' '.$lastName);
    }

    public function computeAge(?string $dateOfBirth): ?int
    {
        if (! filled($dateOfBirth)) {
            return null;
        }

        return (int) now()->parse($dateOfBirth)->age;
    }
}
