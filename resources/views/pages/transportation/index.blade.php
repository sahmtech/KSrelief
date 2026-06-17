@extends('layouts.admin')

@section('title', __('transportation.title'))

@section('content')
<x-page-header
    :title="__('transportation.title')"
    :subtitle="__('transportation.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('transportation.title')],
    ]"
>
    @can('create', \App\Models\TransportationTrip::class)
    <a href="{{ route('operations.transportation.create') }}" class="btn btn-primary btn-sm">
        <i class="ti ti-plus me-1"></i> {{ __('transportation.actions.create') }}
    </a>
    @endcan
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('transportation.stats.total')" :value="$stats['total']" icon="ti ti-route" variant="primary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('transportation.stats.today')" :value="$stats['today']" icon="ti ti-calendar-event" variant="secondary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('transportation.stats.upcoming')" :value="$stats['upcoming']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('transportation.stats.completed')" :value="$stats['completed']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('transportation.stats.patients_transported')" :value="$stats['patients_transported']" icon="ti ti-user-heart" variant="primary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('transportation.stats.members_transported')" :value="$stats['members_transported']" icon="ti ti-stethoscope" variant="success" />
    </div>
</div>

<x-card :title="__('transportation.filters.search')" :compact="true" class="mb-3">
    <form method="GET" action="{{ route('operations.transportation.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">{{ __('transportation.filters.search') }}</label>
            <input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] }}" placeholder="{{ __('transportation.filters.search_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('transportation.filters.campaign') }}</label>
            <select name="campaign_id" class="form-select form-select-sm">
                <option value="">{{ __('transportation.filters.all_campaigns') }}</option>
                @foreach($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected((string) $filters['campaign_id'] === (string) $campaign->id)>{{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">{{ __('transportation.filters.date_from') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small">{{ __('transportation.filters.date_to') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small">{{ __('transportation.filters.trip_type') }}</label>
            <select name="trip_type" class="form-select form-select-sm">
                <option value="">{{ __('transportation.filters.all_types') }}</option>
                @foreach($tripTypes as $type)
                    <option value="{{ $type->value }}" @selected($filters['trip_type'] === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">{{ __('transportation.filters.status') }}</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">{{ __('transportation.filters.all_statuses') }}</option>
                @foreach($tripStatuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('transportation.filters.from_location') }}</label>
            <select name="from_location_id" class="form-select form-select-sm">
                <option value="">{{ __('transportation.filters.all_locations') }}</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) $filters['from_location_id'] === (string) $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">{{ __('transportation.filters.to_location') }}</label>
            <select name="to_location_id" class="form-select form-select-sm">
                <option value="">{{ __('transportation.filters.all_locations') }}</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) $filters['to_location_id'] === (string) $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('transportation.filters.apply') }}</button>
            <a href="{{ route('operations.transportation.index') }}" class="btn btn-light btn-sm">{{ __('transportation.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <x-datatable id="transportationTable" :options="[
        'order' => [[1, 'desc']],
        'columnDefs' => [['targets' => 8, 'orderable' => false, 'className' => 'text-end table-actions']],
    ]">
        <x-slot:head>
            <tr>
                <th>{{ __('transportation.table.trip_code') }}</th>
                <th>{{ __('transportation.table.trip_date') }}</th>
                <th>{{ __('transportation.table.campaign') }}</th>
                <th>{{ __('transportation.table.from') }}</th>
                <th>{{ __('transportation.table.to') }}</th>
                <th>{{ __('transportation.table.type') }}</th>
                <th>{{ __('transportation.table.passengers') }}</th>
                <th>{{ __('transportation.table.status') }}</th>
                <th>{{ __('transportation.table.actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse($trips as $trip)
        <tr>
            <td><code>{{ $trip->trip_code }}</code></td>
            <td>{{ $trip->trip_date->format('Y-m-d') }}</td>
            <td>{{ $trip->campaign?->name }}</td>
            <td>{{ $trip->fromLocation?->name ?? '—' }}</td>
            <td>{{ $trip->toLocation?->name ?? '—' }}</td>
            <td>{{ $trip->tripTypeLabel() }}</td>
            <td><span class="badge bg-secondary-subtle text-secondary">{{ $trip->passengers_count }}</span></td>
            <td><span class="badge-status {{ $trip->statusBadgeClass() }}">{{ $trip->statusLabel() }}</span></td>
            <td class="text-end">
                <div class="dropdown" data-table-dropdown>
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @can('view', $trip)
                        <li><a class="dropdown-item" href="{{ route('operations.transportation.show', $trip) }}"><i class="ti ti-eye me-2"></i>{{ __('transportation.actions.view') }}</a></li>
                        @endcan
                        @can('update', $trip)
                        <li><a class="dropdown-item" href="{{ route('operations.transportation.edit', $trip) }}"><i class="ti ti-edit me-2"></i>{{ __('transportation.actions.edit') }}</a></li>
                        @endcan
                        @can('delete', $trip)
                        <li>
                            <form method="POST" action="{{ route('operations.transportation.destroy', $trip) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('transportation.messages.confirm_delete') }}">
                                    <i class="ti ti-trash me-2"></i>{{ __('transportation.actions.delete') }}
                                </button>
                            </form>
                        </li>
                        @endcan
                    </ul>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center text-muted py-4">{{ __('transportation.messages.no_trips') }}</td></tr>
        @endforelse
    </x-datatable>
</x-card>
@endsection
