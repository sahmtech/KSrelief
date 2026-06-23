@extends('layouts.admin')

@section('title', __('settings.mri_finding_options.create_title'))

@section('content')
<x-page-header
    :title="__('settings.mri_finding_options.create_title')"
    :subtitle="__('settings.mri_finding_options.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.mri_finding_options.title'), 'url' => route('settings.mri-finding-options.index')],
        ['label' => __('settings.mri_finding_options.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.mri-finding-options.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.mri-finding-options.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.mri-finding-options.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
