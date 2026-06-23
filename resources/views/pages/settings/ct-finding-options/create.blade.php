@extends('layouts.admin')

@section('title', __('settings.ct_finding_options.create_title'))

@section('content')
<x-page-header
    :title="__('settings.ct_finding_options.create_title')"
    :subtitle="__('settings.ct_finding_options.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.ct_finding_options.title'), 'url' => route('settings.ct-finding-options.index')],
        ['label' => __('settings.ct_finding_options.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.ct-finding-options.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.ct-finding-options.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.ct-finding-options.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
