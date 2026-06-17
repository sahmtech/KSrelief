@extends('layouts.admin')

@section('title', __('settings.patient_stages.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.patient_stages.edit_title')"
    :subtitle="__('settings.patient_stages.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.patient_stages.title'), 'url' => route('settings.patient-stages.index')],
        ['label' => $patientStage->name, 'url' => route('settings.patient-stages.show', $patientStage)],
        ['label' => __('settings.patient_stages.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('stage_settings.view')<a href="{{ route('settings.patient-stages.show', $patientStage) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-eye me-1"></i> {{ __('settings.actions.view') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<form method="POST" action="{{ route('settings.patient-stages.update', $patientStage) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.patient-stages.partials.form', ['patientStage' => $patientStage])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.patient-stages.show', $patientStage) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
