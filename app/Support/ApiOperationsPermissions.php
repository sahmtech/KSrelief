<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\TransportationTrip;
use App\Models\User;

final class ApiOperationsPermissions
{
    /**
     * @return array<string, bool>
     */
    public static function forAttendance(User $user, ?Attendance $attendance = null): array
    {
        return [
            'can_view' => $user->can('attendance.view'),
            'can_create' => $user->can('attendance.create'),
            'can_update' => $attendance ? $user->can('update', $attendance) : $user->can('attendance.update'),
            'can_delete' => $attendance ? $user->can('delete', $attendance) : $user->can('attendance.delete'),
            'can_export' => $user->can('attendance.export'),
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function forTrip(User $user, ?TransportationTrip $trip = null): array
    {
        return [
            'can_view' => $user->can('transportation.view'),
            'can_create' => $user->can('transportation.create'),
            'can_update' => $trip ? $user->can('update', $trip) : $user->can('transportation.update'),
            'can_delete' => $trip ? $user->can('delete', $trip) : $user->can('transportation.delete'),
            'can_manage_passengers' => $trip ? $user->can('managePassengers', $trip) : $user->can('transportation.manage_passengers'),
            'can_change_status' => $trip ? $user->can('changeStatus', $trip) : $user->can('transportation.change_status'),
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function forActivity(User $user, ?Activity $activity = null): array
    {
        return [
            'can_view' => $user->can('activity.view'),
            'can_create' => $user->can('activity.create'),
            'can_update' => $activity ? $user->can('update', $activity) : $user->can('activity.update'),
            'can_delete' => $activity ? $user->can('delete', $activity) : $user->can('activity.delete'),
            'can_manage_participants' => $activity ? $user->can('manageParticipants', $activity) : $user->can('activity.manage_participants'),
            'can_change_status' => $activity ? $user->can('changeStatus', $activity) : $user->can('activity.change_status'),
        ];
    }
}
