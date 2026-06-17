<?php

namespace App\Http\Controllers\Administration;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\ChangePasswordRequest;
use App\Http\Requests\Administration\StoreUserRequest;
use App\Http\Requests\Administration\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionRegistry;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->filter($request->query('status'), $request->query('role'))
            ->latest()
            ->get();

        return view('pages.administration.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
            'filters' => [
                'status' => $request->query('status'),
                'role' => $request->query('role'),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('pages.administration.users.create', [
            'roles' => Role::orderBy('name')->get(),
            'genders' => Gender::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->userService->createUser(
            $validated,
            $validated['roles'],
            $request->file('avatar')
        );

        return redirect()
            ->route('administration.users.index')
            ->with('success', __('users.messages.created'));
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['roles.permissions']);

        $allPermissions = $user->getAllPermissions()->sortBy('name');

        $groupedPermissions = collect(PermissionRegistry::GROUPS)
            ->map(function (array $groupPermissions, string $group) use ($allPermissions): array {
                $granted = $allPermissions->whereIn('name', $groupPermissions);

                return [
                    'key' => $group,
                    'label' => __('permissions.groups.'.$group),
                    'granted' => $granted->values(),
                    'total' => count($groupPermissions),
                ];
            })
            ->filter(fn (array $group): bool => $group['granted']->isNotEmpty())
            ->values();

        return view('pages.administration.users.show', [
            'user' => $user,
            'permissions' => $allPermissions,
            'groupedPermissions' => $groupedPermissions,
            'stats' => [
                'roles_count' => $user->roles->count(),
                'permissions_count' => $allPermissions->count(),
                'member_days' => $user->created_at->diffInDays(now()),
            ],
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load('roles');

        return view('pages.administration.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'genders' => Gender::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $this->userService->updateUser(
            $user,
            $validated,
            $validated['roles'],
            $request->file('avatar'),
            $request->boolean('remove_avatar')
        );

        if (auth()->id() === $user->id) {
            auth()->setUser($user->fresh(['roles']));
        }

        return redirect()
            ->route('administration.users.show', $user)
            ->with('success', __('users.messages.updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userService->deleteUser($user);

        return redirect()
            ->route('administration.users.index')
            ->with('success', __('users.messages.deleted'));
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('activate', $user);

        $this->userService->activateUser($user);

        return back()->with('success', __('users.messages.activated'));
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $this->userService->deactivateUser($user);

        return back()->with('success', __('users.messages.deactivated'));
    }

    public function updatePassword(ChangePasswordRequest $request, User $user): RedirectResponse
    {
        $this->userService->updatePassword($user, $request->validated('password'));

        return back()->with('success', __('users.messages.password_updated'));
    }
}
