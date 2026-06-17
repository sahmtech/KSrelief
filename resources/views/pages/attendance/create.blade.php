@extends('layouts.admin')

@section('title', __('attendance.create_title'))

@section('content')
<x-page-header
    :title="__('attendance.create_title')"
    :subtitle="__('attendance.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('attendance.title'), 'url' => route('operations.attendance.index')],
        ['label' => __('attendance.create_title')],
    ]"
/>

<x-card>
    <form method="POST" action="{{ route('operations.attendance.store') }}">
        @csrf
        @include('pages.attendance.partials.form', [
            'campaigns' => $campaigns,
            'members' => $members,
            'attendanceStatuses' => $attendanceStatuses,
            'selectedCampaignId' => $selectedCampaignId,
            'formAction' => route('operations.attendance.create'),
        ])
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('common.save') }}</button>
            <a href="{{ route('operations.attendance.index') }}" class="btn btn-light">{{ __('common.cancel') }}</a>
        </div>
    </form>
</x-card>
@endsection
