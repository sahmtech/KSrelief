@extends('layouts.admin')

@section('title', __('settings.insertion_approaches.create_title'))

@section('content')
<x-page-header
    :title="__('settings.insertion_approaches.create_title')"
    :subtitle="__('settings.insertion_approaches.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.insertion_approaches.title'), 'url' => route('settings.insertion-approaches.index')],
        ['label' => __('settings.insertion_approaches.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.insertion-approaches.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.insertion-approaches.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.insertion-approaches.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
