@extends('layouts.admin')

@section('title', __('settings.member_roles.edit_title'))

@section('content')
<x-page-header
    :title="__('settings.member_roles.edit_title')"
    :subtitle="__('settings.member_roles.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.member_roles.title'), 'url' => route('settings.member-roles.index')],
        ['label' => $memberRole->name, 'url' => route('settings.member-roles.show', $memberRole)],
        ['label' => __('settings.member_roles.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('member_role.view')<a href="{{ route('settings.member-roles.show', $memberRole) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-eye me-1"></i> {{ __('settings.actions.view') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<form method="POST" action="{{ route('settings.member-roles.update', $memberRole) }}">
    @csrf @method('PUT')
    <x-card :title="__('settings.sections.details')">@include('pages.settings.member-roles.partials.form', ['memberRole' => $memberRole])</x-card>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('settings.member-roles.show', $memberRole) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
