@extends('layouts.admin')

@section('title', __('settings.insertion_approaches.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.insertion_approaches.edit_title')"
    :subtitle="__('settings.insertion_approaches.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.insertion_approaches.title'), 'url' => route('settings.insertion-approaches.index')],
        ['label' => $approach->name, 'url' => route('settings.insertion-approaches.show', $approach)],
        ['label' => __('settings.insertion_approaches.edit_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.insertion-approaches.update', $approach) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.insertion-approaches.partials.form', ['approach' => $approach])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.insertion-approaches.show', $approach) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
