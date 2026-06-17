<?php

namespace App\Policies\Concerns;

use App\Models\User;

abstract class SettingPolicy
{
    abstract protected function permissionPrefix(): string;

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionPrefix().'.view');
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->can($this->permissionPrefix().'.view');
    }

    public function create(User $user): bool
    {
        return $user->can($this->permissionPrefix().'.create');
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->can($this->permissionPrefix().'.update');
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->can($this->permissionPrefix().'.delete');
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->can($this->permissionPrefix().'.update');
    }
}
