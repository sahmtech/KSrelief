@extends('layouts.admin')

@section('title', __('attendance.show_title'))

@section('content')
<x-page-header
    :title="__('attendance.show_title')"
    :subtitle="$attendance->member?->full_name"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('attendance.title'), 'url' => route('operations.attendance.index')],
        ['label' => $attendance->attendance_date->format('Y-m-d')],
    ]"
>
    @can('update', $attendance)
    <a href="{{ route('operations.attendance.edit', $attendance) }}" class="btn btn-warning btn-sm">
        <i class="ti ti-edit me-1"></i> {{ __('attendance.actions.edit') }}
    </a>
    @endcan
</x-page-header>

<div class="row g-3">
    <div class="col-lg-6">
        <x-card :title="__('common.details')">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.campaign') }}</div>
                    <div class="user-info-list__value">
                        <a href="{{ route('campaigns.show', $attendance->campaign) }}" class="text-decoration-none">{{ $attendance->campaign?->name }}</a>
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.member') }}</div>
                    <div class="user-info-list__value">
                        <a href="{{ route('medical-staff.members.show', $attendance->member) }}" class="text-decoration-none">{{ $attendance->member?->full_name }}</a>
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.table.role') }}</div>
                    <div class="user-info-list__value">{{ $attendance->member?->memberRole?->name ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.date') }}</div>
                    <div class="user-info-list__value">{{ $attendance->attendance_date->format('Y-m-d') }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.shift') }}</div>
                    <div class="user-info-list__value">{{ $attendance->shift_number }}</div>
                </div>
            </div>
        </x-card>
    </div>
    <div class="col-lg-6">
        <x-card :title="__('attendance.fields.status')">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.status') }}</div>
                    <div class="user-info-list__value">
                        @if($attendance->attendanceStatus)
                            <span class="badge border" style="background-color: {{ $attendance->attendanceStatus->color }}20; color: {{ $attendance->attendanceStatus->color }};">
                                {{ $attendance->attendanceStatus->name }}
                            </span>
                        @else — @endif
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.check_in') }}</div>
                    <div class="user-info-list__value">{{ $attendance->checkInLabel() }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.check_out') }}</div>
                    <div class="user-info-list__value">{{ $attendance->checkOutLabel() }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.worked_hours') }}</div>
                    <div class="user-info-list__value">{{ $attendance->workedHoursLabel() }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.recorded_by') }}</div>
                    <div class="user-info-list__value">{{ $attendance->recorder?->name ?? '—' }}</div>
                </div>
                @if($attendance->notes)
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('attendance.fields.notes') }}</div>
                    <div class="user-info-list__value">{{ $attendance->notes }}</div>
                </div>
                @endif
            </div>
        </x-card>
    </div>
</div>
@endsection
