@extends('layouts.admin')

@section('title', __('dashboard.title'))

@section('content')
<x-page-header
    :title="$dashboard['presentation']['title']"
    :subtitle="$dashboard['presentation']['subtitle']"
    :breadcrumbs="[['label' => __('menu.dashboard')]]"
>
    <x-slot:actions>
        @can('campaign.create')
        <a href="{{ route('campaigns.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-plus me-1"></i> {{ __('dashboard.new_campaign') }}
        </a>
        @endcan
    </x-slot:actions>
</x-page-header>

@include('dashboard.partials.executive-content')
@endsection
