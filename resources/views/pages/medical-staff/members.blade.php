@extends('layouts.admin')

@section('title', __('pages.members.title'))

@section('content')
<x-page-header
    :title="__('pages.members.title')"
    :subtitle="__('pages.members.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.medical_staff')],
        ['label' => __('pages.members.title')],
    ]"
>
    <x-slot:actions>
        <button type="button" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i> {{ __('pages.members.add') }}
        </button>
    </x-slot:actions>
</x-page-header>

<x-card>
    <div class="empty-state">
        <div class="empty-state__icon"><i class="ti ti-stethoscope"></i></div>
        <h3 class="empty-state__title">{{ __('pages.members.empty_title') }}</h3>
        <p class="empty-state__text">{{ __('pages.members.empty_text') }}</p>
    </div>
</x-card>
@endsection
