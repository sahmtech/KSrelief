@extends('layouts.admin')

@section('title', __('settings.transportation_locations.create_title'))

@section('content')
<x-page-header
    :title="__('settings.transportation_locations.create_title')"
    :subtitle="__('settings.transportation_locations.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.transportation_locations.title'), 'url' => route('settings.transportation-locations.index')],
        ['label' => __('settings.transportation_locations.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.transportation-locations.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.transportation-locations.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.transportation-locations.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
