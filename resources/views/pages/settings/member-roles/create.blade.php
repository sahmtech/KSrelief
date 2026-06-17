@extends('layouts.admin')

@section('title', __('settings.member_roles.create_title'))

@section('content')
<x-page-header
    :title="__('settings.member_roles.create_title')"
    :subtitle="__('settings.member_roles.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.member_roles.title'), 'url' => route('settings.member-roles.index')],
        ['label' => __('settings.member_roles.create_title')],
    ]"
/>
<form method="POST" action="{{ route('settings.member-roles.store') }}">
    @csrf
    <x-card :title="__('settings.sections.details')">@include('pages.settings.member-roles.partials.form')</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('settings.member-roles.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
