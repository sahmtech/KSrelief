<?php

namespace App\Http\Controllers\Administration;

use App\Enums\SystemRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreRoleRequest;
use App\Http\Requests\Administration\UpdateRoleRequest;
use App\Models\Role;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $editRolePayload = null;

        if (old('_modal') === 'edit' && old('_role_id')) {
            $editRole = Role::with('permissions')->find(old('_role_id'));

            if ($editRole) {
                $editRolePayload = [
                    'id' => $editRole->id,
                    'name' => old('name', $editRole->name),
                    'label' => SystemRole::tryFrom($editRole->name)?->label() ?? $editRole->name,
                    'is_system' => in_array($editRole->name, SystemRole::values(), true),
                    'permissions' => old('permissions', $editRole->permissions->pluck('name')->all()),
                ];
            }
        }

        return view('pages.administration.roles', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'permissionGroups' => PermissionRegistry::GROUPS,
            'reopenCreateModal' => old('_modal') === 'create',
            'editRolePayload' => $editRolePayload,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => PermissionRegistry::GUARD,
        ]);

        $role->syncPermissions($this->ensurePermissionsExist($request->validated('permissions', [])));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('administration.roles.index')
            ->with('success', __('pages.roles.created'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        if (! $request->isSystemRole()) {
            $role->update([
                'name' => $request->validated('name'),
            ]);
        }

        $role->syncPermissions($this->ensurePermissionsExist($request->validated('permissions', [])));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('administration.roles.index')
            ->with('success', __('pages.roles.updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if (in_array($role->name, \App\Enums\SystemRole::values(), true)) {
            return redirect()
                ->route('administration.roles.index')
                ->with('error', __('pages.roles.cannot_delete_system'));
        }

        if ($role->users()->exists()) {
            return redirect()
                ->route('administration.roles.index')
                ->with('error', __('pages.roles.cannot_delete_assigned'));
        }

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('administration.roles.index')
            ->with('success', __('pages.roles.deleted'));
    }

    /** @param  list<string>  $permissions */
    private function ensurePermissionsExist(array $permissions): array
    {
        foreach ($permissions as $permission) {
            \App\Models\Permission::findOrCreate($permission, PermissionRegistry::GUARD);
        }

        return $permissions;
    }
}
