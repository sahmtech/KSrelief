<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * Create a new user and assign roles.
     * Audit: log user.created event here in future.
     */
    public function createUser(array $data, array $roleIds = [], ?UploadedFile $avatar = null): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'gender' => $data['gender'],
            'status' => $data['status'] ?? UserStatus::Active->value,
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        if ($avatar) {
            $user->update([
                'avatar' => $this->storeAvatar($user, $avatar),
            ]);
        }

        $this->assignRoles($user, $roleIds);

        return $user->load('roles');
    }

    /**
     * Update user profile and sync roles.
     * Audit: log user.updated event here in future.
     */
    public function updateUser(
        User $user,
        array $data,
        array $roleIds = [],
        ?UploadedFile $avatar = null,
        bool $removeAvatar = false
    ): User {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'gender' => $data['gender'],
            'status' => $data['status'],
        ]);

        if ($removeAvatar) {
            $this->deleteAvatar($user);
        } elseif ($avatar) {
            $user->update([
                'avatar' => $this->storeAvatar($user, $avatar),
            ]);
        }

        $this->assignRoles($user, $roleIds);

        return $user->load('roles');
    }

    /**
     * Sync Spatie roles by role names.
     *
     * @param  list<string>  $roleNames
     */
    public function assignRoles(User $user, array $roleNames): void
    {
        $user->syncRoles($roleNames);
    }

    /**
     * Update user password.
     * Audit: log user.password_changed event here in future.
     */
    public function updatePassword(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }

    /**
     * Activate user account.
     * Audit: log user.activated event here in future.
     */
    public function activateUser(User $user): User
    {
        $user->update(['status' => UserStatus::Active->value]);

        return $user;
    }

    /**
     * Deactivate user account.
     * Audit: log user.deactivated event here in future.
     */
    public function deactivateUser(User $user): User
    {
        $user->update(['status' => UserStatus::Inactive->value]);

        return $user;
    }

    /**
     * Delete a user and detach role assignments.
     * Audit: log user.deleted event here in future.
     */
    public function deleteUser(User $user): void
    {
        $user->syncRoles([]);
        $user->syncPermissions([]);
        $user->delete();
    }

    /**
     * Record successful login timestamp.
     */
    public function recordLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    private function storeAvatar(User $user, UploadedFile $file): string
    {
        $this->deleteAvatarFile($user->avatar);

        return $file->store('avatars/'.$user->id, 'public');
    }

    private function deleteAvatar(User $user): void
    {
        $this->deleteAvatarFile($user->avatar);

        $user->update(['avatar' => null]);
    }

    private function deleteAvatarFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
