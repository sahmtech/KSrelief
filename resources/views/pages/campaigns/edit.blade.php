@extends('layouts.admin')

@section('title', __('campaigns.edit_title'))

@section('content')
<x-page-header
    :title="__('campaigns.edit_title')"
    :subtitle="__('campaigns.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.campaign_management')],
        ['label' => __('campaigns.title'), 'url' => route('campaigns.index')],
        ['label' => $campaign->name, 'url' => route('campaigns.show', $campaign)],
        ['label' => __('campaigns.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('view', $campaign)
            <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-eye me-1"></i> {{ __('campaigns.actions.view') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

@if($campaign->isTerminalStatus())
    <div class="alert alert-warning mb-4">
        <i class="ti ti-lock me-1"></i> {{ __('campaigns.messages.locked_completed') }}
    </div>
@endif

<form method="POST" action="{{ route('campaigns.update', $campaign) }}">
    @csrf
    @method('PUT')

    @include('pages.campaigns.partials.form', [
        'campaign' => $campaign,
        'statuses' => $statuses,
    ])

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
