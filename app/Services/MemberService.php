<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\Member;
use App\Models\MemberRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MemberService
{
    /**
     * Audit event placeholders — wire to audit log module in future.
     */
    public const AUDIT_CREATED = 'member.created';

    public const AUDIT_UPDATED = 'member.updated';

    public const AUDIT_DELETED = 'member.deleted';

    public const AUDIT_ASSIGNED = 'member.campaign.assigned';

    public const AUDIT_REMOVED = 'member.campaign.removed';

    public const AUDIT_ROLE_CHANGED = 'member.role.changed';

    public function createMember(array $data, User $user): Member
    {
        return DB::transaction(function () use ($data, $user): Member {
            $member = Member::create([
                ...$this->prepareMemberData($data),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Future: dispatch audit log event self::AUDIT_CREATED

            return $member->load(['memberRole', 'specialty', 'user', 'creator']);
        });
    }

    public function updateMember(Member $member, array $data, User $user): Member
    {
        return DB::transaction(function () use ($member, $data, $user): Member {
            $previousRoleId = $member->member_role_id;

            $member->update([
                ...$this->prepareMemberData($data, $member),
                'updated_by' => $user->id,
            ]);

            if ($previousRoleId !== $member->member_role_id) {
                // Future: dispatch audit log event self::AUDIT_ROLE_CHANGED
            }

            // Future: dispatch audit log event self::AUDIT_UPDATED

            return $member->fresh(['memberRole', 'specialty', 'user', 'creator', 'updater']);
        });
    }

    public function deleteMember(Member $member): void
    {
        DB::transaction(function () use ($member): void {
            $member->campaignAssignments()->delete();
            $member->delete();

            // Future: dispatch audit log event self::AUDIT_DELETED
        });
    }

    public function assignToCampaign(Member $member, Campaign $campaign, array $data, User $user): CampaignMember
    {
        return DB::transaction(function () use ($member, $campaign, $data, $user): CampaignMember {
            if (CampaignMember::query()
                ->where('campaign_id', $campaign->id)
                ->where('member_id', $member->id)
                ->exists()) {
                throw new \InvalidArgumentException(__('members.messages.already_assigned'));
            }

            $assignment = CampaignMember::create([
                'campaign_id' => $campaign->id,
                'member_id' => $member->id,
                'assigned_role' => $data['assigned_role'] ?? $member->memberRole?->name,
                'assigned_from' => $data['assigned_from'] ?? now()->toDateString(),
                'assigned_to' => $data['assigned_to'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // Future: dispatch audit log event self::AUDIT_ASSIGNED

            return $assignment->load(['campaign', 'member.memberRole', 'member.specialty']);
        });
    }

    public function removeFromCampaign(Member $member, Campaign $campaign): void
    {
        DB::transaction(function () use ($member, $campaign): void {
            $deleted = CampaignMember::query()
                ->where('campaign_id', $campaign->id)
                ->where('member_id', $member->id)
                ->delete();

            if ($deleted === 0) {
                throw new \InvalidArgumentException(__('members.messages.not_assigned'));
            }

            // Future: dispatch audit log event self::AUDIT_REMOVED
        });
    }

    /**
     * @return array<string, int>
     */
    public function getDashboardStats(): array
    {
        $roleCodes = MemberRole::query()
            ->whereIn('code', ['doctor', 'specialist', 'coordinator'])
            ->pluck('id', 'code');

        return [
            'total' => Member::count(),
            'active' => Member::where('status', MemberStatus::Active)->count(),
            'doctors' => Member::where('member_role_id', $roleCodes['doctor'] ?? 0)->count(),
            'specialists' => Member::where('member_role_id', $roleCodes['specialist'] ?? 0)->count(),
            'coordinators' => Member::where('member_role_id', $roleCodes['coordinator'] ?? 0)->count(),
            'assigned_to_campaigns' => CampaignMember::query()->distinct('member_id')->count('member_id'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareMemberData(array $data, ?Member $member = null): array
    {
        $firstName = $data['first_name'];
        $lastName = $data['last_name'];
        $dateOfBirth = $data['date_of_birth'] ?? null;

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($firstName.' '.$lastName),
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $dateOfBirth,
            'age' => $this->computeAge($dateOfBirth),
            'nationality' => $data['nationality'] ?? null,
            'member_role_id' => $data['member_role_id'],
            'specialty_id' => $data['specialty_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'status' => $data['status'] ?? MemberStatus::Active->value,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function computeAge(?string $dateOfBirth): ?int
    {
        if (! filled($dateOfBirth)) {
            return null;
        }

        return (int) now()->parse($dateOfBirth)->age;
    }
}
