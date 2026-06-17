@extends('layouts.admin')

@section('title', __('campaigns.title'))

@section('content')
@php
    $sortLink = function (string $column) use ($filters): string {
        $direction = ($filters['sort'] === $column && $filters['direction'] === 'asc') ? 'desc' : 'asc';

        return request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $direction,
        ]);
    };
@endphp

<x-page-header
    :title="__('campaigns.title')"
    :subtitle="__('campaigns.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.campaign_management')],
        ['label' => __('campaigns.title')],
    ]"
>
    <x-slot:actions>
        @can('create', \App\Models\Campaign::class)
            <a href="{{ route('campaigns.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> {{ __('campaigns.add') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('campaigns.stats.total')" :value="$stats['total']" icon="ti ti-flag" variant="primary" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('campaigns.stats.active')" :value="$stats['active']" icon="ti ti-activity" variant="success" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('campaigns.stats.completed')" :value="$stats['completed']" icon="ti ti-circle-check" variant="secondary" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('campaigns.stats.cancelled')" :value="$stats['cancelled']" icon="ti ti-ban" variant="danger" />
    </div>
    <div class="col-sm-6 col-xl">
        <x-stats-card :label="__('campaigns.stats.upcoming')" :value="$stats['upcoming']" icon="ti ti-calendar-event" variant="warning" />
    </div>
</div>

<x-card :title="__('campaigns.filters.title')" :compact="true" class="mb-4">
    <form method="GET" action="{{ route('campaigns.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label class="form-group-admin__label">{{ __('campaigns.filters.search') }}</label>
            <input type="search" name="search" class="form-group-admin__input" value="{{ $filters['search'] }}" placeholder="{{ __('campaigns.filters.search_placeholder') }}">
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('campaigns.filters.status') }}</label>
            <select name="status" class="form-group-admin__input">
                <option value="">{{ __('campaigns.filters.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->code }}" @selected($filters['status'] === $status->code)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-4">
            <label class="form-group-admin__label">{{ __('campaigns.filters.specialty') }}</label>
            <select name="specialty_id" class="form-group-admin__input">
                <option value="">{{ __('campaigns.filters.all_specialties') }}</option>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected((string) $filters['specialty_id'] === (string) $specialty->id)>{{ $specialty->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <x-location-picker
                prefix="filter_"
                :selected-country-id="$filters['country_id']"
                :selected-city-id="$filters['city_id']"
                :selected-country-name="$filterCountry?->localizedName()"
                :selected-city-name="$filterCity?->localizedName()"
                :required="false"
                :can-add-city="false"
                :country-label="__('campaigns.filters.country')"
                :city-label="__('campaigns.filters.city')"
            />
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('campaigns.filters.start_from') }}</label>
            <input type="date" name="start_from" class="form-group-admin__input" value="{{ $filters['start_from'] }}">
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('campaigns.filters.end_to') }}</label>
            <input type="date" name="end_to" class="form-group-admin__input" value="{{ $filters['end_to'] }}">
        </div>
        <div class="col-lg-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('campaigns.filters.apply') }}</button>
            <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('campaigns.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <div class="admin-table-scroll">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th><a href="{{ $sortLink('name') }}" class="text-decoration-none text-reset">{{ __('campaigns.table.name') }}</a></th>
                    <th>{{ __('campaigns.table.country') }}</th>
                    <th>{{ __('campaigns.table.city') }}</th>
                    <th>{{ __('campaigns.table.specialty') }}</th>
                    <th><a href="{{ $sortLink('start_date') }}" class="text-decoration-none text-reset">{{ __('campaigns.table.start_date') }}</a></th>
                    <th><a href="{{ $sortLink('end_date') }}" class="text-decoration-none text-reset">{{ __('campaigns.table.end_date') }}</a></th>
                    <th><a href="{{ $sortLink('shifts_count') }}" class="text-decoration-none text-reset">{{ __('campaigns.table.shifts') }}</a></th>
                    <th><a href="{{ $sortLink('expected_patients') }}" class="text-decoration-none text-reset">{{ __('campaigns.table.expected_patients') }}</a></th>
                    <th><a href="{{ $sortLink('status') }}" class="text-decoration-none text-reset">{{ __('campaigns.table.status') }}</a></th>
                    <th>{{ __('campaigns.table.created_by') }}</th>
                    <th class="text-end table-actions">{{ __('campaigns.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                    <tr>
                        <td class="fw-medium text-truncate" style="max-width: 180px;">{{ $campaign->name }}</td>
                        <td class="text-nowrap">{{ $campaign->country?->localizedName() ?? '—' }}</td>
                        <td class="text-nowrap">{{ $campaign->city?->localizedName() ?? '—' }}</td>
                        <td class="text-nowrap">{{ $campaign->specialty?->name ?? '—' }}</td>
                        <td class="text-nowrap">{{ $campaign->start_date->format('Y-m-d') }}</td>
                        <td class="text-nowrap">{{ $campaign->end_date->format('Y-m-d') }}</td>
                        <td>{{ $campaign->shifts_count }}</td>
                        <td>{{ number_format($campaign->expected_patients) }}</td>
                        <td class="text-nowrap">
                            <span class="badge-status {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span>
                        </td>
                        <td class="text-truncate" style="max-width: 140px;">{{ $campaign->creator?->name ?? '—' }}</td>
                        <td class="text-end table-actions">
                            <div class="dropdown">
                                <button
                                    class="btn btn-sm btn-outline-secondary"
                                    type="button"
                                    data-table-dropdown
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    aria-label="{{ __('campaigns.table.actions') }}"
                                >
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    @can('view', $campaign)
                                        <li><a class="dropdown-item" href="{{ route('campaigns.show', $campaign) }}"><i class="ti ti-eye me-2"></i>{{ __('campaigns.actions.view') }}</a></li>
                                    @endcan
                                    @can('update', $campaign)
                                        <li><a class="dropdown-item" href="{{ route('campaigns.edit', $campaign) }}"><i class="ti ti-pencil me-2"></i>{{ __('campaigns.actions.edit') }}</a></li>
                                    @endcan
                                    @can('delete', $campaign)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('campaigns.destroy', $campaign) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('campaigns.messages.confirm_delete') }}">
                                                    <i class="ti ti-trash me-2"></i>{{ __('campaigns.actions.delete') }}
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
                        <td colspan="11" class="text-center text-muted py-5">{{ __('campaigns.messages.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($campaigns->hasPages())
        <div class="admin-card__footer">
            {{ $campaigns->links() }}
        </div>
    @endif
</x-card>
@endsection
