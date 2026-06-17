@extends('layouts.admin')

@section('title', __('settings.cities.create_title'))

@section('content')
<x-page-header
    :title="__('settings.cities.create_title')"
    :subtitle="__('settings.cities.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.cities.title'), 'url' => route('settings.cities.index')],
        ['label' => __('settings.cities.create_title')],
    ]"
/>

<form method="POST" action="{{ route('settings.cities.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">
        @include('pages.settings.cities.partials.form', ['countries' => $countries])
    </x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.cities.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
