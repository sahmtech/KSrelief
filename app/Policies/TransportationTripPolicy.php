<?php

namespace App\Policies;

use App\Models\TransportationTrip;
use App\Models\User;

class TransportationTripPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('transportation.view');
    }

    public function view(User $user, TransportationTrip $trip): bool
    {
        return $user->can('transportation.view');
    }

    public function create(User $user): bool
    {
        return $user->can('transportation.create');
    }

    public function update(User $user, TransportationTrip $trip): bool
    {
        return $user->can('transportation.update');
    }

    public function delete(User $user, TransportationTrip $trip): bool
    {
        return $user->can('transportation.delete');
    }

    public function managePassengers(User $user, TransportationTrip $trip): bool
    {
        return $user->can('transportation.manage_passengers');
    }

    public function changeStatus(User $user, TransportationTrip $trip): bool
    {
        return $user->can('transportation.change_status');
    }
}
