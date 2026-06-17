<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('activity.view');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->can('activity.view');
    }

    public function create(User $user): bool
    {
        return $user->can('activity.create');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->can('activity.update');
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->can('activity.delete');
    }

    public function manageParticipants(User $user, Activity $activity): bool
    {
        return $user->can('activity.manage_participants');
    }

    public function changeStatus(User $user, Activity $activity): bool
    {
        return $user->can('activity.change_status');
    }
}
