@extends('layouts.admin')

@section('title', __('pages.attendance.title'))

@section('content')
<x-page-header
    :title="__('pages.attendance.title')"
    :subtitle="__('pages.attendance.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('pages.attendance.title')],
    ]"
/>

<x-card>
    <div class="empty-state">
        <div class="empty-state__icon"><i class="ti ti-clock"></i></div>
        <h3 class="empty-state__title">{{ __('pages.attendance.empty_title') }}</h3>
        <p class="empty-state__text">{{ __('pages.attendance.empty_text') }}</p>
    </div>
</x-card>
@endsection
