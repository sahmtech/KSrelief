@extends('layouts.admin')

@section('title', __('members.create_title'))

@section('content')
<x-page-header
    :title="__('members.create_title')"
    :subtitle="__('members.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.medical_staff')],
        ['label' => __('members.title'), 'url' => route('medical-staff.members.index')],
        ['label' => __('members.create_title')],
    ]"
/>

<form method="POST" action="{{ route('medical-staff.members.store') }}">
    @csrf
    @include('pages.medical-staff.members.partials.form', [
        'memberRoles' => $memberRoles,
        'specialties' => $specialties,
        'statuses' => $statuses,
        'users' => $users,
    ])
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('medical-staff.members.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
