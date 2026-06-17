@extends('layouts.admin')

@section('title', __('locations.title'))

@section('content')
<x-page-header
    :title="__('locations.title')"
    :subtitle="__('locations.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('locations.title')],
    ]"
/>

<x-card :title="__('locations.countries')" :compact="true" class="mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-8">
            <label class="form-group-admin__label">{{ __('locations.search_countries') }}</label>
            <input type="search" name="search" class="form-group-admin__input" value="{{ $search }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('campaigns.filters.apply') }}</button>
            <a href="{{ route('administration.locations.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('campaigns.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('locations.fields.country') }}</th>
                    <th>{{ __('locations.fields.code') }}</th>
                    <th>{{ __('locations.cities') }}</th>
                    <th class="text-end">{{ __('campaigns.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($countries as $country)
                    <tr>
                        <td class="fw-medium">{{ $country->localizedName() }}</td>
                        <td><code>{{ $country->code }}</code></td>
                        <td>{{ __('locations.city_count', ['count' => $country->cities_count]) }}</td>
                        <td class="text-end">
                            <a href="{{ route('administration.locations.show', $country) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-map-pin me-1"></i> {{ __('locations.cities') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">{{ __('campaigns.messages.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($countries->hasPages())
        <div class="admin-card__footer">{{ $countries->links() }}</div>
    @endif
</x-card>
@endsection
