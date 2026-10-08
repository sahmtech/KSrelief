<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\PatientAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class AuthenticatedUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['roles', 'member.memberRole', 'member.campaigns']);

        $member = $this->member;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'gender' => $this->gender?->value,
            'avatar_url' => $this->avatarUrl(),
            'status' => $this->status?->value,
            'roles' => $this->roles->pluck('name')->values(),
            'role_labels' => $this->roleLabels(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'member' => $member ? [
                'id' => $member->id,
                'member_role_code' => $member->memberRole?->code,
                'member_role_name' => $member->memberRole?->name,
                'campaign_ids' => $member->campaigns->pluck('id')->values(),
            ] : null,
            'campaign_assignment_ids' => $this->campaignAssignments()->pluck('campaign_id')->values(),
            'patient_visibility' => app(PatientAccessService::class)->visibilityMeta($this->resource),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
        ];
    }
}
