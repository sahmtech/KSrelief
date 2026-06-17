@extends('layouts.admin')

@section('title', __('members.edit_title'))

@section('content')
<x-page-header
    :title="__('members.edit_title')"
    :subtitle="__('members.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.medical_staff')],
        ['label' => __('members.title'), 'url' => route('medical-staff.members.index')],
        ['label' => $member->full_name, 'url' => route('medical-staff.members.show', $member)],
        ['label' => __('members.edit_title')],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('medical-staff.members.show', $member) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-eye me-1"></i> {{ __('members.actions.view') }}
        </a>
    </x-slot:actions>
</x-page-header>

<form method="POST" action="{{ route('medical-staff.members.update', $member) }}">
    @csrf
    @method('PUT')
    @include('pages.medical-staff.members.partials.form', [
        'member' => $member,
        'memberRoles' => $memberRoles,
        'specialties' => $specialties,
        'statuses' => $statuses,
        'users' => $users,
    ])
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('medical-staff.members.show', $member) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
