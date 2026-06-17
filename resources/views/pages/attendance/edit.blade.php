@extends('layouts.admin')

@section('title', __('attendance.edit_title'))

@section('content')
<x-page-header
    :title="__('attendance.edit_title')"
    :subtitle="$attendance->member?->full_name"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('attendance.title'), 'url' => route('operations.attendance.index')],
        ['label' => __('attendance.edit_title')],
    ]"
/>

<x-card>
    <form method="POST" action="{{ route('operations.attendance.update', $attendance) }}">
        @csrf @method('PUT')
        @include('pages.attendance.partials.form', [
            'attendance' => $attendance,
            'campaigns' => $campaigns,
            'members' => $members,
            'attendanceStatuses' => $attendanceStatuses,
            'selectedCampaignId' => $attendance->campaign_id,
            'formAction' => route('operations.attendance.edit', $attendance),
        ])
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('common.save') }}</button>
            <a href="{{ route('operations.attendance.index') }}" class="btn btn-light">{{ __('common.cancel') }}</a>
        </div>
    </form>
</x-card>
@endsection
