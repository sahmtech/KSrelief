@extends('layouts.admin')

@section('title', __('attendance.title'))

@section('content')
<x-page-header
    :title="__('attendance.title')"
    :subtitle="__('attendance.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('attendance.title')],
    ]"
>
    @can('create', \App\Models\Attendance::class)
    <a href="{{ route('operations.attendance.quick') }}" class="btn btn-primary btn-sm">
        <i class="ti ti-clipboard-check me-1"></i> {{ __('attendance.actions.quick') }}
    </a>
    <a href="{{ route('operations.attendance.create') }}" class="btn btn-outline-primary btn-sm">
        <i class="ti ti-plus me-1"></i> {{ __('attendance.actions.record') }}
    </a>
    @endcan
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.total')" :value="$stats['total']" icon="ti ti-list" variant="primary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.present_today')" :value="$stats['present_today']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.late_today')" :value="$stats['late_today']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.absent_today')" :value="$stats['absent_today']" icon="ti ti-circle-x" variant="danger" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.leave_today')" :value="$stats['leave_today']" icon="ti ti-calendar-off" variant="secondary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.attendance_rate')" :value="$stats['attendance_rate'] . '%'" icon="ti ti-percentage" variant="primary" />
    </div>
</div>

<x-card :title="__('attendance.filters.search')" :compact="true" class="mb-3">
    <form method="GET" action="{{ route('operations.attendance.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">{{ __('attendance.filters.search') }}</label>
            <input type="text" name="search" class="form-control form-control-sm"
                   value="{{ $filters['search'] }}" placeholder="{{ __('attendance.filters.search_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('attendance.filters.campaign') }}</label>
            <select name="campaign_id" class="form-select form-select-sm">
                <option value="">{{ __('attendance.filters.all_campaigns') }}</option>
                @foreach($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected((string) $filters['campaign_id'] === (string) $campaign->id)>{{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">{{ __('attendance.filters.date_from') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small">{{ __('attendance.filters.date_to') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small">{{ __('attendance.filters.shift') }}</label>
            <input type="number" name="shift_number" min="1" class="form-control form-control-sm" value="{{ $filters['shift_number'] }}" placeholder="{{ __('attendance.filters.all_shifts') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('attendance.filters.status') }}</label>
            <select name="attendance_status_id" class="form-select form-select-sm">
                <option value="">{{ __('attendance.filters.all_statuses') }}</option>
                @foreach($attendanceStatuses as $status)
                    <option value="{{ $status->id }}" @selected((string) $filters['attendance_status_id'] === (string) $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('attendance.filters.role') }}</label>
            <select name="member_role_id" class="form-select form-select-sm">
                <option value="">{{ __('attendance.filters.all_roles') }}</option>
                @foreach($memberRoles as $role)
                    <option value="{{ $role->id }}" @selected((string) $filters['member_role_id'] === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('attendance.filters.specialty') }}</label>
            <select name="specialty_id" class="form-select form-select-sm">
                <option value="">{{ __('attendance.filters.all_specialties') }}</option>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected((string) $filters['specialty_id'] === (string) $specialty->id)>{{ $specialty->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('attendance.filters.apply') }}</button>
            <a href="{{ route('operations.attendance.index') }}" class="btn btn-light btn-sm">{{ __('attendance.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <x-datatable id="attendanceTable" :options="[
        'order' => [[0, 'desc']],
        'columnDefs' => [['targets' => 11, 'orderable' => false, 'className' => 'text-end']],
    ]">
        <x-slot:head>
            <tr>
                <th>{{ __('attendance.table.date') }}</th>
                <th>{{ __('attendance.table.campaign') }}</th>
                <th>{{ __('attendance.table.member') }}</th>
                <th>{{ __('attendance.table.role') }}</th>
                <th>{{ __('attendance.table.specialty') }}</th>
                <th>{{ __('attendance.table.shift') }}</th>
                <th>{{ __('attendance.table.check_in') }}</th>
                <th>{{ __('attendance.table.check_out') }}</th>
                <th>{{ __('attendance.table.worked_hours') }}</th>
                <th>{{ __('attendance.table.status') }}</th>
                <th>{{ __('attendance.table.recorded_by') }}</th>
                <th class="text-end">{{ __('attendance.table.actions') }}</th>
            </tr>
        </x-slot:head>
        @foreach($attendances as $attendance)
        <tr>
            <td class="text-nowrap">{{ $attendance->attendance_date->format('Y-m-d') }}</td>
            <td>{{ $attendance->campaign?->name }}</td>
            <td>
                <a href="{{ route('medical-staff.members.show', $attendance->member) }}" class="text-decoration-none fw-medium">
                    {{ $attendance->member?->full_name }}
                </a>
            </td>
            <td>{{ $attendance->member?->memberRole?->name ?? '—' }}</td>
            <td>{{ $attendance->member?->specialty?->name ?? '—' }}</td>
            <td>{{ $attendance->shift_number }}</td>
            <td>{{ $attendance->checkInLabel() }}</td>
            <td>{{ $attendance->checkOutLabel() }}</td>
            <td>{{ $attendance->workedHoursLabel() }}</td>
            <td>
                @if($attendance->attendanceStatus)
                    <span class="badge border" style="background-color: {{ $attendance->attendanceStatus->color }}20; color: {{ $attendance->attendanceStatus->color }};">
                        {{ $attendance->attendanceStatus->name }}
                    </span>
                @else — @endif
            </td>
            <td class="small">{{ $attendance->recorder?->name ?? '—' }}</td>
            <td class="text-end table-actions">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-table-dropdown data-bs-toggle="dropdown" aria-label="{{ __('attendance.table.actions') }}">
                        <i class="ti ti-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        @can('view', $attendance)
                        <li><a class="dropdown-item" href="{{ route('operations.attendance.show', $attendance) }}"><i class="ti ti-eye me-2"></i>{{ __('attendance.actions.view') }}</a></li>
                        @endcan
                        @can('update', $attendance)
                        <li><a class="dropdown-item" href="{{ route('operations.attendance.edit', $attendance) }}"><i class="ti ti-edit me-2"></i>{{ __('attendance.actions.edit') }}</a></li>
                        @endcan
                        @can('delete', $attendance)
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('operations.attendance.destroy', $attendance) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('attendance.messages.confirm_delete') }}">
                                    <i class="ti ti-trash me-2"></i>{{ __('attendance.actions.delete') }}
                                </button>
                            </form>
                        </li>
                        @endcan
                    </ul>
                </div>
            </td>
        </tr>
        @endforeach
    </x-datatable>
</x-card>
@endsection
