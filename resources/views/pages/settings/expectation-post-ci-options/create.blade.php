@extends('layouts.admin')

@section('title', __('settings.expectation_post_ci_options.create_title'))

@section('content')
<x-page-header
    :title="__('settings.expectation_post_ci_options.create_title')"
    :subtitle="__('settings.expectation_post_ci_options.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.expectation_post_ci_options.title'), 'url' => route('settings.expectation-post-ci-options.index')],
        ['label' => __('settings.expectation_post_ci_options.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.expectation-post-ci-options.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.expectation-post-ci-options.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.expectation-post-ci-options.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
