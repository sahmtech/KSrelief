@extends('layouts.admin')

@section('title', __('settings.specialties.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.specialties.edit_title')"
    :subtitle="__('settings.specialties.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.specialties.title'), 'url' => route('settings.specialties.index')],
        ['label' => $specialty->name, 'url' => route('settings.specialties.show', $specialty)],
        ['label' => __('settings.specialties.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('specialty.view')<a href="{{ route('settings.specialties.show', $specialty) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-eye me-1"></i> {{ __('settings.actions.view') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<form method="POST" action="{{ route('settings.specialties.update', $specialty) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.specialties.partials.form', ['specialty' => $specialty])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.specialties.show', $specialty) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
