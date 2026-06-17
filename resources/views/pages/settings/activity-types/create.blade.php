@extends('layouts.admin')

@section('title', __('settings.activity_types.create_title'))

@section('content')
<x-page-header
    :title="__('settings.activity_types.create_title')"
    :subtitle="__('settings.activity_types.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.activity_types.title'), 'url' => route('settings.activity-types.index')],
        ['label' => __('settings.activity_types.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.activity-types.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.activity-types.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.activity-types.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
