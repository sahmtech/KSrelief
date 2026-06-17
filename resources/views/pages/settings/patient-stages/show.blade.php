@extends('layouts.admin')

@section('title', $patientStage->name)

@section('content')
<x-page-header
    :title="__('settings.patient_stages.show_title')"
    :subtitle="__('settings.patient_stages.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.patient_stages.title'), 'url' => route('settings.patient-stages.index')],
        ['label' => $patientStage->name],
    ]"
>
    <x-slot:actions>
        @can('stage_settings.update')<a href="{{ route('settings.patient-stages.edit', $patientStage) }}" class="btn btn-primary btn-sm"><i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}</a>@endcan
        @can('stage_settings.delete')
            <form method="POST" action="{{ route('settings.patient-stages.destroy', $patientStage) }}" class="d-inline">@csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('settings.messages.confirm_delete') }}"><i class="ti ti-trash me-1"></i> {{ __('settings.actions.delete') }}</button>
            </form>
        @endcan
    </x-slot:actions>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt><dd class="col-sm-8">{{ $patientStage->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.code') }}</dt><dd class="col-sm-8"><code>{{ $patientStage->code }}</code></dd>
                <dt class="col-sm-4">{{ __('settings.fields.description') }}</dt><dd class="col-sm-8">{{ $patientStage->description ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.color') }}</dt>
                <dd class="col-sm-8">
                    <span class="d-inline-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 16px; height: 16px; background-color: {{ $patientStage->color }};"></span>
                        <code>{{ $patientStage->color }}</code>
                    </span>
                </dd>
                <dt class="col-sm-4">{{ __('settings.fields.sort_order') }}</dt><dd class="col-sm-8">{{ $patientStage->sort_order }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.is_default') }}</dt><dd class="col-sm-8">{{ $patientStage->is_default ? __('settings.messages.yes') : __('settings.messages.no') }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8"><span class="badge-status {{ $patientStage->status->badgeClass() }}">{{ $patientStage->status->label() }}</span></dd>
            </dl>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('settings.sections.audit')">
            <dl class="row mb-0">
                <dt class="col-sm-5">{{ __('settings.fields.created_at') }}</dt><dd class="col-sm-7">{{ $patientStage->created_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_at') }}</dt><dd class="col-sm-7">{{ $patientStage->updated_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.created_by') }}</dt><dd class="col-sm-7">{{ $patientStage->creator?->name ?? '—' }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_by') }}</dt><dd class="col-sm-7">{{ $patientStage->updater?->name ?? '—' }}</dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
