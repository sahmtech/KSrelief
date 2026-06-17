@extends('layouts.admin')

@section('title', __('settings.transportation_locations.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.transportation_locations.edit_title')"
    :subtitle="__('settings.transportation_locations.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.transportation_locations.title'), 'url' => route('settings.transportation-locations.index')],
        ['label' => $transportationLocation->name, 'url' => route('settings.transportation-locations.show', $transportationLocation)],
        ['label' => __('settings.transportation_locations.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('transport_location.view')<a href="{{ route('settings.transportation-locations.show', $transportationLocation) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-eye me-1"></i> {{ __('settings.actions.view') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<form method="POST" action="{{ route('settings.transportation-locations.update', $transportationLocation) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.transportation-locations.partials.form', ['transportationLocation' => $transportationLocation])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.transportation-locations.show', $transportationLocation) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
