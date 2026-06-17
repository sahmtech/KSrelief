@extends('layouts.admin')

@section('title', __('members.title'))

@section('content')
<x-page-header
    :title="__('members.title')"
    :subtitle="__('members.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.medical_staff')],
        ['label' => __('members.title')],
    ]"
>
    <x-slot:actions>
        @can('import', \App\Models\Member::class)
            <a href="{{ route('medical-staff.members.import') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-file-spreadsheet me-1"></i> {{ __('members.actions.import_excel') }}
            </a>
        @endcan
        @can('create', \App\Models\Member::class)
            <a href="{{ route('medical-staff.members.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> {{ __('members.add') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('members.stats.total')" :value="$stats['total']" icon="ti ti-users" variant="primary" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('members.stats.active')" :value="$stats['active']" icon="ti ti-user-check" variant="success" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('members.stats.doctors')" :value="$stats['doctors']" icon="ti ti-stethoscope" variant="secondary" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('members.stats.specialists')" :value="$stats['specialists']" icon="ti ti-heartbeat" variant="secondary" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('members.stats.coordinators')" :value="$stats['coordinators']" icon="ti ti-users-group" variant="warning" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('members.stats.assigned')" :value="$stats['assigned_to_campaigns']" icon="ti ti-flag" variant="primary" />
    </div>
</div>

<x-card :title="__('members.filters.title')" :compact="true" class="mb-4">
    <form method="GET" action="{{ route('medical-staff.members.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label class="form-group-admin__label">{{ __('members.filters.search') }}</label>
            <input type="search" name="search" class="form-group-admin__input" value="{{ $filters['search'] }}" placeholder="{{ __('members.filters.search_placeholder') }}">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-group-admin__label">{{ __('members.filters.role') }}</label>
            <select name="member_role_id" class="form-group-admin__input">
                <option value="">{{ __('members.filters.all_roles') }}</option>
                @foreach($memberRoles as $role)
                    <option value="{{ $role->id }}" @selected((string) $filters['member_role_id'] === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-group-admin__label">{{ __('members.filters.specialty') }}</label>
            <select name="specialty_id" class="form-group-admin__input">
                <option value="">{{ __('members.filters.all_specialties') }}</option>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected((string) $filters['specialty_id'] === (string) $specialty->id)>{{ $specialty->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-group-admin__label">{{ __('members.filters.status') }}</label>
            <select name="status" class="form-group-admin__input">
                <option value="">{{ __('members.filters.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('members.filters.apply') }}</button>
            <a href="{{ route('medical-staff.members.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('members.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <x-datatable
        id="membersTable"
        :options="[
            'order' => [[0, 'asc']],
            'columnDefs' => [
                ['targets' => 8, 'orderable' => false, 'width' => '60px', 'className' => 'text-end'],
            ],
        ]"
    >
        <x-slot:head>
            <tr>
                <th>{{ __('members.table.name') }}</th>
                <th>{{ __('members.table.role') }}</th>
                <th>{{ __('members.table.specialty') }}</th>
                <th>{{ __('members.table.mobile') }}</th>
                <th>{{ __('members.table.email') }}</th>
                <th>{{ __('members.table.status') }}</th>
                <th>{{ __('members.table.campaigns') }}</th>
                <th>{{ __('members.table.created_at') }}</th>
                <th class="text-end">{{ __('members.table.actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse($members as $member)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <x-member-avatar :member="$member" size="sm" />
                        <a href="{{ route('medical-staff.members.show', $member) }}" class="fw-medium text-decoration-none text-truncate">
                            {{ $member->full_name }}
                        </a>
                    </div>
                </td>
                <td>{{ $member->memberRole?->name ?? '—' }}</td>
                <td>{{ $member->specialty?->name ?? '—' }}</td>
                <td><code>{{ $member->mobile }}</code></td>
                <td>{{ $member->email ?? '—' }}</td>
                <td><span class="badge-status {{ $member->statusBadgeClass() }}">{{ $member->statusLabel() }}</span></td>
                <td>
                    <span class="badge bg-light text-dark border">{{ $member->campaign_assignments_count }}</span>
                </td>
                <td>{{ $member->created_at->format('Y-m-d') }}</td>
                <td class="text-end table-actions">
                    <div class="dropdown">
                        <button
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            data-table-dropdown
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-label="{{ __('members.table.actions') }}"
                        >
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item" href="{{ route('medical-staff.members.show', $member) }}">
                                    <i class="ti ti-eye me-2"></i>{{ __('members.actions.view') }}
                                </a>
                            </li>
                            @can('update', $member)
                                <li>
                                    <a class="dropdown-item" href="{{ route('medical-staff.members.edit', $member) }}">
                                        <i class="ti ti-pencil me-2"></i>{{ __('members.actions.edit') }}
                                    </a>
                                </li>
                            @endcan
                            @can('assignCampaign', $member)
                                <li>
                                    <a class="dropdown-item" href="{{ route('medical-staff.members.campaigns', $member) }}">
                                        <i class="ti ti-flag me-2"></i>{{ __('members.actions.assign_campaigns') }}
                                    </a>
                                </li>
                            @endcan
                            @can('delete', $member)
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('medical-staff.members.destroy', $member) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('members.messages.confirm_delete') }}">
                                            <i class="ti ti-trash me-2"></i>{{ __('members.actions.delete') }}
                                        </button>
                                    </form>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">{{ __('members.messages.empty') }}</td>
            </tr>
        @endforelse
    </x-datatable>
</x-card>
@endsection
