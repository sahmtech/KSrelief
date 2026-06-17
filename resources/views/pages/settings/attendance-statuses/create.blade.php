@extends('layouts.admin')

@section('title', __('settings.attendance_statuses.create_title'))

@section('content')
<x-page-header
    :title="__('settings.attendance_statuses.create_title')"
    :subtitle="__('settings.attendance_statuses.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.attendance_statuses.title'), 'url' => route('settings.attendance-statuses.index')],
        ['label' => __('settings.attendance_statuses.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.attendance-statuses.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.attendance-statuses.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.attendance-statuses.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
