@extends('layouts.admin')

@section('title', __('settings.dashboard.title'))

@section('content')
@php
    $cardIcons = [
        'countries' => 'ti-world',
        'cities' => 'ti-building-community',
        'specialties' => 'ti-stethoscope',
        'member_roles' => 'ti-users-group',
        'patient_eligibility_statuses' => 'ti-user-check',
        'patient_stages' => 'ti-stairs',
        'activity_types' => 'ti-activity',
        'transportation_locations' => 'ti-bus',
        'attendance_statuses' => 'ti-calendar-check',
        'campaign_statuses' => 'ti-flag',
    ];
@endphp

<x-page-header
    :title="__('settings.dashboard.title')"
    :subtitle="__('settings.dashboard.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title')],
    ]"
/>

<div class="row g-3">
    @foreach($cards as $card)
        @can($card['permission'])
            <div class="col-sm-6 col-xl-4">
                <x-card :compact="true" class="h-100">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="stats-card__icon stats-card__icon--primary">
                            <i class="ti {{ $cardIcons[$card['key']] ?? 'ti-settings' }}"></i>
                        </div>
                        <a href="{{ route($card['route']) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-arrow-right me-1"></i> {{ __('settings.dashboard.quick_access') }}
                        </a>
                    </div>
                    <h3 class="h6 fw-semibold mb-3">{{ $card['label'] }}</h3>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small">{{ __('settings.dashboard.total') }}</div>
                            <div class="fs-5 fw-semibold">{{ number_format($card['total']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">{{ __('settings.dashboard.active') }}</div>
                            <div class="fs-5 fw-semibold text-success">{{ number_format($card['active']) }}</div>
                        </div>
                    </div>
                </x-card>
            </div>
        @endcan
    @endforeach
</div>

@if(config('app.debug') && $debugBackfill)
    <x-card :title="__('settings.debug.title')" class="mt-3 border border-warning-subtle">
        <p class="text-muted mb-3" style="font-size: 0.875rem;">{{ __('settings.debug.backfill_description') }}</p>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-md-4">
                <div class="text-muted small">{{ __('settings.debug.missing_campaign_codes') }}</div>
                <div class="fs-5 fw-semibold">{{ number_format($debugBackfill['campaigns']) }}</div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="text-muted small">{{ __('settings.debug.missing_patient_codes') }}</div>
                <div class="fs-5 fw-semibold">{{ number_format($debugBackfill['patients']) }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.backfill-record-codes') }}">
            @csrf
            <button
                type="submit"
                class="btn btn-warning btn-sm"
                @disabled($debugBackfill['campaigns'] === 0 && $debugBackfill['patients'] === 0)
                data-confirm="{{ __('settings.debug.backfill_confirm') }}"
            >
                <i class="ti ti-barcode me-1"></i>{{ __('settings.debug.backfill_action') }}
            </button>
        </form>
    </x-card>
@endif
@endsection
