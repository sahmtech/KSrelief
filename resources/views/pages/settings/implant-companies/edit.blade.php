@extends('layouts.admin')

@section('title', __('settings.implant_companies.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.implant_companies.edit_title')"
    :subtitle="__('settings.implant_companies.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.implant_companies.title'), 'url' => route('settings.implant-companies.index')],
        ['label' => $company->name, 'url' => route('settings.implant-companies.show', $company)],
        ['label' => __('settings.implant_companies.edit_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.implant-companies.update', $company) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.implant-companies.partials.form', ['company' => $company])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.implant-companies.show', $company) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
