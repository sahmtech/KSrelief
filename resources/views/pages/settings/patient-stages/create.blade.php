@extends('layouts.admin')

@section('title', __('settings.patient_stages.create_title'))

@section('content')
<x-page-header
    :title="__('settings.patient_stages.create_title')"
    :subtitle="__('settings.patient_stages.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.patient_stages.title'), 'url' => route('settings.patient-stages.index')],
        ['label' => __('settings.patient_stages.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.patient-stages.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.patient-stages.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.patient-stages.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
