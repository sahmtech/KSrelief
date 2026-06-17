@extends('layouts.admin')

@section('title', __('settings.cities.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.cities.edit_title')"
    :subtitle="__('settings.cities.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.cities.title'), 'url' => route('settings.cities.index')],
        ['label' => $city->localizedName(), 'url' => route('settings.cities.show', $city)],
        ['label' => __('settings.cities.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('city.view')
            <a href="{{ route('settings.cities.show', $city) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-eye me-1"></i> {{ __('settings.actions.view') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<form method="POST" action="{{ route('settings.cities.update', $city) }}">
    @csrf
    @method('PUT')
    <x-card :title="__('settings.sections.details')">
        @include('pages.settings.cities.partials.form', ['city' => $city, 'countries' => $countries])
    </x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.cities.show', $city) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
