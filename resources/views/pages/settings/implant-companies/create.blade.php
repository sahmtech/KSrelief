@extends('layouts.admin')

@section('title', __('settings.implant_companies.create_title'))

@section('content')
<x-page-header
    :title="__('settings.implant_companies.create_title')"
    :subtitle="__('settings.implant_companies.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.implant_companies.title'), 'url' => route('settings.implant-companies.index')],
        ['label' => __('settings.implant_companies.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.implant-companies.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.implant-companies.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.implant-companies.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
