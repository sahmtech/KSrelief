<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PatientAccessService
{
    public function enforcesCampaignScope(): bool
    {
        return (bool) config('mobile_api.enforce_campaign_scope', true);
    }

    public function canViewAllCampaigns(User $user): bool
    {
        if (! $this->enforcesCampaignScope()) {
            return true;
        }

        return $user->hasAnyRole(['super_admin', 'campaign_manager']);
    }

    /**
     * @return list<int>|null null = all campaigns allowed
     */
    public function allowedCampaignIds(User $user): ?array
    {
        if ($this->canViewAllCampaigns($user)) {
            return null;
        }

        $ids = collect();

        $user->loadMissing('member.campaigns');

        if ($user->member) {
            $ids = $ids->merge($user->member->campaigns()->pluck('campaigns.id'));
        }

        $ids = $ids->merge(
            $user->campaignAssignments()->pluck('campaign_id')
        );

        return $ids->unique()->filter()->values()->all();
    }

    /**
     * @param  Builder<Patient>  $query
     * @return Builder<Patient>
     */
    public function scopeVisiblePatients(Builder $query, User $user): Builder
    {
        $allowed = $this->allowedCampaignIds($user);

        if ($allowed === null) {
            return $query;
        }

        if ($allowed === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('campaign_id', $allowed);
    }

    public function assertCanAccessPatient(User $user, Patient $patient): void
    {
        $this->assertCanAccessCampaign($user, (int) $patient->campaign_id, __('patients.api.messages.patient_not_in_campaign'));
    }

    public function assertCanAccessCampaign(User $user, int $campaignId, ?string $message = null): void
    {
        $allowed = $this->allowedCampaignIds($user);

        if ($allowed === null) {
            return;
        }

        if (! in_array($campaignId, $allowed, true)) {
            abort(403, $message ?? __('patients.api.messages.campaign_not_accessible'));
        }
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeVisibleByCampaign(Builder $query, User $user, string $column = 'campaign_id'): Builder
    {
        $allowed = $this->allowedCampaignIds($user);

        if ($allowed === null) {
            return $query;
        }

        if ($allowed === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $allowed);
    }

    /**
     * @return array{mode: string, campaign_ids: list<int>}
     */
    public function visibilityMeta(User $user): array
    {
        $allowed = $this->allowedCampaignIds($user);

        return [
            'mode' => $allowed === null ? 'all' : 'assigned_campaigns',
            'campaign_ids' => $allowed ?? [],
        ];
    }
}
