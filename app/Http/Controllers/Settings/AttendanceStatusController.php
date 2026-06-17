<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreAttendanceStatusRequest;
use App\Http\Requests\Settings\UpdateAttendanceStatusRequest;
use App\Models\AttendanceStatus;
use App\Services\Settings\AttendanceStatusSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceStatusController extends Controller
{
    public function __construct(
        private readonly AttendanceStatusSettingService $attendanceStatusSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceStatus::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.attendance-statuses.index', [
            'attendanceStatuses' => $this->attendanceStatusSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AttendanceStatus::class);

        return view('pages.settings.attendance-statuses.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreAttendanceStatusRequest $request): RedirectResponse
    {
        $this->attendanceStatusSettingService->create($request->validated());

        return redirect()
            ->route('settings.attendance-statuses.index')
            ->with('success', __('settings.entities.attendance_statuses.messages.created'));
    }

    public function show(AttendanceStatus $attendanceStatus): View
    {
        $this->authorize('view', $attendanceStatus);

        $attendanceStatus->load(['creator', 'updater']);

        return view('pages.settings.attendance-statuses.show', [
            'attendanceStatus' => $attendanceStatus,
        ]);
    }

    public function edit(AttendanceStatus $attendanceStatus): View
    {
        $this->authorize('update', $attendanceStatus);

        return view('pages.settings.attendance-statuses.edit', [
            'attendanceStatus' => $attendanceStatus,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateAttendanceStatusRequest $request, AttendanceStatus $attendanceStatus): RedirectResponse
    {
        $this->attendanceStatusSettingService->update($attendanceStatus, $request->validated());

        return redirect()
            ->route('settings.attendance-statuses.show', $attendanceStatus)
            ->with('success', __('settings.entities.attendance_statuses.messages.updated'));
    }

    public function destroy(AttendanceStatus $attendanceStatus): RedirectResponse
    {
        $this->authorize('delete', $attendanceStatus);

        $this->attendanceStatusSettingService->delete($attendanceStatus);

        return redirect()
            ->route('settings.attendance-statuses.index')
            ->with('success', __('settings.entities.attendance_statuses.messages.deleted'));
    }
}
