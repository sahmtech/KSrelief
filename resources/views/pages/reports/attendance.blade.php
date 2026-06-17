@extends('layouts.admin')

@section('title', __('pages.attendance_reports.title'))

@section('content')
<x-page-header
    :title="__('pages.attendance_reports.title')"
    :subtitle="__('pages.attendance_reports.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.reports')],
        ['label' => __('pages.attendance_reports.title')],
    ]"
>
    <x-slot:actions>
        <button type="button" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-download me-1"></i> {{ __('common.export') }}
        </button>
    </x-slot:actions>
</x-page-header>

<x-card>
    <div class="empty-state">
        <div class="empty-state__icon"><i class="ti ti-calendar-stats"></i></div>
        <h3 class="empty-state__title">{{ __('pages.attendance_reports.empty_title') }}</h3>
        <p class="empty-state__text">{{ __('pages.attendance_reports.empty_text') }}</p>
    </div>
</x-card>
@endsection
