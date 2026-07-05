<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\ActivityType;
use App\Models\AttendanceStatus;
use App\Models\CampaignMember;
use App\Models\CampaignStatusRecord;
use App\Models\City;
use App\Models\Country;
use App\Models\CtFindingOption;
use App\Models\ExpectationPostCiOption;
use App\Models\ImplantCompany;
use App\Models\ImplantElectrodeType;
use App\Models\InsertionApproach;
use App\Models\MriFindingOption;
use App\Models\Member;
use App\Models\MemberRole;
use App\Models\PatientEligibilityStatus;
use App\Models\PatientStage;
use App\Models\Specialty;
use App\Models\TransportationLocation;
use Illuminate\Database\Eloquent\Collection;

class LookupService
{
    /**
     * @return Collection<int, Country>
     */
    public function getCountries(?string $term = null, int $limit = 100): Collection
    {
        return Country::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, City>
     */
    public function getCities(?int $countryId = null, ?string $term = null, int $limit = 100): Collection
    {
        return City::query()
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Specialty>
     */
    public function getSpecialties(?string $term = null, int $limit = 100): Collection
    {
        return Specialty::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(?string $term = null, ?string $status = null, int $limit = 100): Collection
    {
        return Member::query()
            ->with(['memberRole', 'specialty'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when(! $status, fn ($query) => $query->where('status', MemberStatus::Active->value))
            ->search($term)
            ->orderBy('full_name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, MemberRole>
     */
    public function getMemberRoles(?string $term = null, int $limit = 100): Collection
    {
        return MemberRole::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, PatientEligibilityStatus>
     */
    public function getPatientEligibilityStatuses(?string $term = null, int $limit = 100): Collection
    {
        return PatientEligibilityStatus::query()
            ->active()
            ->search($term)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, PatientStage>
     */
    public function getPatientStages(?string $term = null, int $limit = 100): Collection
    {
        return PatientStage::query()
            ->active()
            ->search($term)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ActivityType>
     */
    public function getActivityTypes(?string $term = null, int $limit = 100): Collection
    {
        return ActivityType::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, TransportationLocation>
     */
    public function getTransportationLocations(
        ?string $type = null,
        ?string $term = null,
        int $limit = 100
    ): Collection {
        return TransportationLocation::query()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, AttendanceStatus>
     */
    public function getAttendanceStatuses(?string $term = null, int $limit = 100): Collection
    {
        return AttendanceStatus::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{
     *     doctors: Collection<int, Member>,
     *     specialists: Collection<int, Member>,
     *     coordinators: Collection<int, Member>,
     * }
     */
    public function getCampaignTeamMembers(int $campaignId): array
    {
        if ($campaignId <= 0) {
            return [
                'doctors' => collect(),
                'specialists' => collect(),
                'coordinators' => collect(),
            ];
        }

        $assignments = CampaignMember::query()
            ->with(['member.memberRole', 'member.specialty'])
            ->where('campaign_id', $campaignId)
            ->whereHas('member', fn ($query) => $query->where('status', MemberStatus::Active->value))
            ->get()
            ->filter(fn (CampaignMember $assignment) => $assignment->isActiveOn(now()));

        $grouped = [
            'doctors' => collect(),
            'specialists' => collect(),
            'coordinators' => collect(),
        ];

        foreach ($assignments as $assignment) {
            $member = $assignment->member;

            if (! $member) {
                continue;
            }

            if ($this->memberMatchesCampaignRole($member, $assignment->assigned_role, 'doctor')) {
                $grouped['doctors']->push($member);
            }

            if ($this->memberMatchesCampaignRole($member, $assignment->assigned_role, 'specialist')) {
                $grouped['specialists']->push($member);
            }

            if ($this->memberMatchesCampaignRole($member, $assignment->assigned_role, 'coordinator')) {
                $grouped['coordinators']->push($member);
            }
        }

        foreach ($grouped as $key => $members) {
            $grouped[$key] = $members->unique('id')->sortBy('full_name')->values();
        }

        return $grouped;
    }

    private function memberMatchesCampaignRole(Member $member, ?string $assignedRole, string $roleCode): bool
    {
        if ($member->memberRole?->code === $roleCode) {
            return true;
        }

        return $this->assignedRoleMatchesCode($assignedRole, $roleCode);
    }

    private function assignedRoleMatchesCode(?string $assignedRole, string $roleCode): bool
    {
        if ($assignedRole === null || trim($assignedRole) === '') {
            return false;
        }

        $normalized = mb_strtolower(trim($assignedRole));

        if ($normalized === $roleCode) {
            return true;
        }

        static $roleLabels = null;

        if ($roleLabels === null) {
            $roleLabels = MemberRole::query()
                ->active()
                ->get(['code', 'name'])
                ->mapWithKeys(fn (MemberRole $role): array => [
                    $role->code => array_unique(array_filter([
                        mb_strtolower($role->code),
                        mb_strtolower($role->name),
                    ])),
                ])
                ->all();
        }

        $labels = $roleLabels[$roleCode] ?? [mb_strtolower($roleCode)];

        return in_array($normalized, $labels, true);
    }

    /**
     * @return Collection<int, CampaignStatusRecord>
     */
    public function getCampaignStatuses(?string $term = null, int $limit = 100): Collection
    {
        return CampaignStatusRecord::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ImplantCompany>
     */
    public function getImplantCompanies(?string $term = null, int $limit = 100): Collection
    {
        return ImplantCompany::query()
            ->active()
            ->search($term)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, InsertionApproach>
     */
    public function getInsertionApproaches(?string $term = null, int $limit = 100): Collection
    {
        return InsertionApproach::query()
            ->active()
            ->search($term)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, MriFindingOption>
     */
    public function getMriFindingOptions(?string $term = null, int $limit = 100): Collection
    {
        return MriFindingOption::query()
            ->active()
            ->search($term)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ExpectationPostCiOption>
     */
    public function getExpectationPostCiOptions(?string $term = null, int $limit = 100): Collection
    {
        return ExpectationPostCiOption::query()
            ->active()
            ->search($term)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, CtFindingOption>
     */
    public function getCtFindingOptions(?string $term = null, int $limit = 100): Collection
    {
        return CtFindingOption::query()
            ->active()
            ->search($term)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ImplantElectrodeType>
     */
    public function getImplantElectrodeTypes(?int $companyId = null, int $limit = 100): Collection
    {
        return ImplantElectrodeType::query()
            ->when($companyId, fn ($query) => $query->where('implant_company_id', $companyId))
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
