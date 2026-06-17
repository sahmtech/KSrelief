@extends('layouts.admin')

@section('title', $campaign->name.' — '.__('dashboard.campaign_title'))

@section('content')
<x-page-header
    :title="__('dashboard.campaign_title')"
    :subtitle="$campaign->name"
    :breadcrumbs="[
        ['label' => __('menu.dashboard'), 'url' => route('dashboard')],
        ['label' => __('campaigns.title'), 'url' => route('campaigns.index')],
        ['label' => $campaign->name, 'url' => route('campaigns.show', $campaign)],
        ['label' => __('dashboard.campaign_title')],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-eye me-1"></i> {{ __('campaigns.actions.view') }}
        </a>
    </x-slot:actions>
</x-page-header>

@include('dashboard.partials.executive-content')
@endsection
