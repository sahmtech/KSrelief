@extends('layouts.admin')

@section('title', $activityType->name)

@section('content')
<x-page-header
    :title="__('settings.activity_types.show_title')"
    :subtitle="__('settings.activity_types.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.activity_types.title'), 'url' => route('settings.activity-types.index')],
        ['label' => $activityType->name],
    ]"
>
    <x-slot:actions>
        @can('activity_type.update')<a href="{{ route('settings.activity-types.edit', $activityType) }}" class="btn btn-primary btn-sm"><i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}</a>@endcan
        @can('activity_type.delete')
            <form method="POST" action="{{ route('settings.activity-types.destroy', $activityType) }}" class="d-inline">@csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('settings.messages.confirm_delete') }}"><i class="ti ti-trash me-1"></i> {{ __('settings.actions.delete') }}</button>
            </form>
        @endcan
    </x-slot:actions>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt><dd class="col-sm-8">{{ $activityType->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.code') }}</dt><dd class="col-sm-8"><code>{{ $activityType->code }}</code></dd>
                <dt class="col-sm-4">{{ __('settings.fields.color') }}</dt>
                <dd class="col-sm-8">
                    <span class="d-inline-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 16px; height: 16px; background-color: {{ $activityType->color }};"></span>
                        <code>{{ $activityType->color }}</code>
                    </span>
                </dd>
                <dt class="col-sm-4">{{ __('settings.fields.description') }}</dt><dd class="col-sm-8">{{ $activityType->description ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8"><span class="badge-status {{ $activityType->status->badgeClass() }}">{{ $activityType->status->label() }}</span></dd>
            </dl>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('settings.sections.audit')">
            <dl class="row mb-0">
                <dt class="col-sm-5">{{ __('settings.fields.created_at') }}</dt><dd class="col-sm-7">{{ $activityType->created_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_at') }}</dt><dd class="col-sm-7">{{ $activityType->updated_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.created_by') }}</dt><dd class="col-sm-7">{{ $activityType->creator?->name ?? '—' }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_by') }}</dt><dd class="col-sm-7">{{ $activityType->updater?->name ?? '—' }}</dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
