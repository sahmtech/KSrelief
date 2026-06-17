<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreMemberRoleRequest;
use App\Http\Requests\Settings\UpdateMemberRoleRequest;
use App\Models\MemberRole;
use App\Services\Settings\MemberRoleSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberRoleController extends Controller
{
    public function __construct(
        private readonly MemberRoleSettingService $memberRoleSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MemberRole::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.member-roles.index', [
            'memberRoles' => $this->memberRoleSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MemberRole::class);

        return view('pages.settings.member-roles.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreMemberRoleRequest $request): RedirectResponse
    {
        $this->memberRoleSettingService->create($request->validated());

        return redirect()
            ->route('settings.member-roles.index')
            ->with('success', __('settings.entities.member_roles.messages.created'));
    }

    public function show(MemberRole $memberRole): View
    {
        $this->authorize('view', $memberRole);

        $memberRole->load(['creator', 'updater']);

        return view('pages.settings.member-roles.show', [
            'memberRole' => $memberRole,
        ]);
    }

    public function edit(MemberRole $memberRole): View
    {
        $this->authorize('update', $memberRole);

        return view('pages.settings.member-roles.edit', [
            'memberRole' => $memberRole,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateMemberRoleRequest $request, MemberRole $memberRole): RedirectResponse
    {
        $this->memberRoleSettingService->update($memberRole, $request->validated());

        return redirect()
            ->route('settings.member-roles.show', $memberRole)
            ->with('success', __('settings.entities.member_roles.messages.updated'));
    }

    public function destroy(MemberRole $memberRole): RedirectResponse
    {
        $this->authorize('delete', $memberRole);

        $this->memberRoleSettingService->delete($memberRole);

        return redirect()
            ->route('settings.member-roles.index')
            ->with('success', __('settings.entities.member_roles.messages.deleted'));
    }
}
