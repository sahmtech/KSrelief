@extends('layouts.admin')

@section('title', __('settings.activity_types.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.activity_types.edit_title')"
    :subtitle="__('settings.activity_types.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.activity_types.title'), 'url' => route('settings.activity-types.index')],
        ['label' => $activityType->name, 'url' => route('settings.activity-types.show', $activityType)],
        ['label' => __('settings.activity_types.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('activity_type.view')<a href="{{ route('settings.activity-types.show', $activityType) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-eye me-1"></i> {{ __('settings.actions.view') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<form method="POST" action="{{ route('settings.activity-types.update', $activityType) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.activity-types.partials.form', ['activityType' => $activityType])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.activity-types.show', $activityType) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
