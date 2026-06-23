<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Models\ActivityType;
use App\Models\AttendanceStatus;
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
        $members = Member::query()
            ->with(['memberRole', 'specialty'])
            ->where('status', MemberStatus::Active->value)
            ->whereHas('campaignAssignments', function ($query) use ($campaignId): void {
                $query->where('campaign_id', $campaignId)
                    ->where(function ($q): void {
                        $q->whereNull('assigned_to')
                            ->orWhereDate('assigned_to', '>=', now());
                    });
            })
            ->orderBy('full_name')
            ->get();

        return [
            'doctors' => $members->filter(fn (Member $m) => $m->memberRole?->code === 'doctor')->values(),
            'specialists' => $members->filter(fn (Member $m) => $m->memberRole?->code === 'specialist')->values(),
            'coordinators' => $members->filter(fn (Member $m) => $m->memberRole?->code === 'coordinator')->values(),
        ];
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
