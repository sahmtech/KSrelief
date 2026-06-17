<?php

namespace App\Policies;

use App\Enums\SystemRole;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('role.view');
    }

    public function create(User $user): bool
    {
        return $user->can('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('role.update');
    }

    public function delete(User $user, Role $role): bool
    {
        if (in_array($role->name, SystemRole::values(), true)) {
            return false;
        }

        return $user->can('role.delete');
    }
}
