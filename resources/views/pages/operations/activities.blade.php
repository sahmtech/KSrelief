@extends('layouts.admin')

@section('title', __('pages.activities.title'))

@section('content')
<x-page-header
    :title="__('pages.activities.title')"
    :subtitle="__('pages.activities.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('pages.activities.title')],
    ]"
/>

<x-card>
    <div class="empty-state">
        <div class="empty-state__icon"><i class="ti ti-activity"></i></div>
        <h3 class="empty-state__title">{{ __('pages.activities.empty_title') }}</h3>
        <p class="empty-state__text">{{ __('pages.activities.empty_text') }}</p>
    </div>
</x-card>
@endsection
