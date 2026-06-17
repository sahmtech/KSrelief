@extends('layouts.admin')

@section('title', __('settings.campaign_statuses.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.campaign_statuses.edit_title')"
    :subtitle="__('settings.campaign_statuses.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.campaign_statuses.title'), 'url' => route('settings.campaign-statuses.index')],
        ['label' => $campaignStatus->name, 'url' => route('settings.campaign-statuses.show', $campaignStatus)],
        ['label' => __('settings.campaign_statuses.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('campaign_status.view')<a href="{{ route('settings.campaign-statuses.show', $campaignStatus) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-eye me-1"></i> {{ __('settings.actions.view') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<form method="POST" action="{{ route('settings.campaign-statuses.update', $campaignStatus) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.campaign-statuses.partials.form', ['campaignStatus' => $campaignStatus])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.campaign-statuses.show', $campaignStatus) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
