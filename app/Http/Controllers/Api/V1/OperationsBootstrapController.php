<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ActivityStatus;
use App\Enums\TripStatus;
use App\Enums\TripType;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\LookupService;
use App\Services\PatientAccessService;
use App\Support\ApiOperationsPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationsBootstrapController extends Controller
{
    public function __construct(
        private readonly LookupService $lookupService,
        private readonly PatientAccessService $campaignAccess,
    ) {}

    public function module(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user->can('attendance.view')
            || $user->can('transportation.view')
            || $user->can('activity.view'),
            403
        );

        $campaignQuery = Campaign::query()->orderBy('name');
        $allowed = $this->campaignAccess->allowedCampaignIds($user);

        if ($allowed !== null) {
            $campaignQuery->whereIn('id', $allowed);
        }

        return response()->json([
            'campaigns' => $campaignQuery->get(['id', 'name', 'code']),
            'campaign_visibility' => $this->campaignAccess->visibilityMeta($user),
            'permissions' => [
                'attendance' => ApiOperationsPermissions::forAttendance($user),
                'transportation' => ApiOperationsPermissions::forTrip($user),
                'activities' => ApiOperationsPermissions::forActivity($user),
            ],
            'attendance_statuses' => $this->lookupService->getAttendanceStatuses(),
            'member_roles' => $this->lookupService->getMemberRoles(),
            'specialties' => $this->lookupService->getSpecialties(),
            'transportation_locations' => $this->lookupService->getTransportationLocations(limit: 200),
            'trip_types' => collect(TripType::cases())->map(fn (TripType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
            'trip_statuses' => collect(TripStatus::cases())->map(fn (TripStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
            'activity_types' => $this->lookupService->getActivityTypes(limit: 200),
            'patient_stages' => $this->lookupService->getPatientStages(limit: 200),
            'activity_statuses' => collect(ActivityStatus::cases())->map(fn (ActivityStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }
}
