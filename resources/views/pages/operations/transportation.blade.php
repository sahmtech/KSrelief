@extends('layouts.admin')

@section('title', __('pages.transportation.title'))

@section('content')
<x-page-header
    :title="__('pages.transportation.title')"
    :subtitle="__('pages.transportation.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('pages.transportation.title')],
    ]"
/>

<x-card>
    <div class="empty-state">
        <div class="empty-state__icon"><i class="ti ti-truck"></i></div>
        <h3 class="empty-state__title">{{ __('pages.transportation.empty_title') }}</h3>
        <p class="empty-state__text">{{ __('pages.transportation.empty_text') }}</p>
    </div>
</x-card>
@endsection
