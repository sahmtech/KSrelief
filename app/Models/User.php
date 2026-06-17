<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'gender',
        'avatar',
        'status',
        'email_verified_at',
        'password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'gender' => Gender::class,
            'status' => UserStatus::class,
        ];
    }

    public function campaignAssignments(): HasMany
    {
        return $this->hasMany(CampaignUser::class);
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function primaryRoleLabel(): string
    {
        $role = $this->roles->first();

        if (! $role) {
            return '';
        }

        return \App\Enums\SystemRole::tryFrom($role->name)?->label() ?? $role->name;
    }

    public function roleLabels(): string
    {
        return $this->roles
            ->map(fn ($role) => \App\Enums\SystemRole::tryFrom($role->name)?->label() ?? $role->name)
            ->implode(', ');
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function initials(): string
    {
        $name = trim($this->name);

        if ($name === '') {
            return '?';
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function hasAvatar(): bool
    {
        return filled($this->avatar);
    }

    public function avatarUrl(): ?string
    {
        if (! $this->hasAvatar()) {
            return null;
        }

        return '/storage/'.str_replace('\\', '/', $this->avatar);
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        });
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeFilter(Builder $query, ?string $status = null, ?string $role = null): Builder
    {
        return $query
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($role, fn (Builder $q) => $q->whereHas('roles', fn (Builder $rq) => $rq->where('name', $role)));
    }
}
