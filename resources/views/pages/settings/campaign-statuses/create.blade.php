@extends('layouts.admin')

@section('title', __('settings.campaign_statuses.create_title'))

@section('content')
<x-page-header
    :title="__('settings.campaign_statuses.create_title')"
    :subtitle="__('settings.campaign_statuses.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.campaign_statuses.title'), 'url' => route('settings.campaign-statuses.index')],
        ['label' => __('settings.campaign_statuses.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.campaign-statuses.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.campaign-statuses.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.campaign-statuses.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
