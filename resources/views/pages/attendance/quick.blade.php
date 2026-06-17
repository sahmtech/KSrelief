@extends('layouts.admin')

@section('title', __('attendance.quick_title'))

@section('content')
<x-page-header
    :title="__('attendance.quick_title')"
    :subtitle="__('attendance.quick_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('attendance.title'), 'url' => route('operations.attendance.index')],
        ['label' => __('attendance.quick_title')],
    ]"
>
    <a href="{{ route('operations.attendance.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ti ti-arrow-left me-1"></i> {{ __('attendance.title') }}
    </a>
</x-page-header>

<x-card :compact="true" class="mb-4">
    <form method="GET" action="{{ route('operations.attendance.quick') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold">{{ __('attendance.fields.campaign') }}</label>
            <select name="campaign_id" class="form-select" required>
                <option value="">{{ __('common.select') }}</option>
                @foreach($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected($campaignId == $campaign->id)>{{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">{{ __('attendance.fields.date') }}</label>
            <input type="date" name="attendance_date" class="form-control" value="{{ $attendanceDate }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">{{ __('attendance.fields.shift') }}</label>
            <input type="number" name="shift_number" class="form-control" min="1" max="10" value="{{ $shiftNumber }}" required>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="ti ti-users me-1"></i> {{ __('attendance.actions.load_members') }}
            </button>
        </div>
    </form>
</x-card>

@if($campaignId && $stats)
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><x-stats-card :label="__('attendance.stats.present')" :value="$stats['present']" icon="ti ti-circle-check" variant="success" /></div>
    <div class="col-6 col-md-3"><x-stats-card :label="__('attendance.stats.late')" :value="$stats['late']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-6 col-md-3"><x-stats-card :label="__('attendance.stats.absent')" :value="$stats['absent']" icon="ti ti-circle-x" variant="danger" /></div>
    <div class="col-6 col-md-3"><x-stats-card :label="__('attendance.stats.attendance_rate')" :value="$stats['attendance_rate'] . '%'" icon="ti ti-percentage" variant="primary" /></div>
</div>
@endif

@if($campaignId)
    @if($members->isEmpty())
        <x-card>
            <div class="text-center text-muted py-5">{{ __('attendance.messages.no_members') }}</div>
        </x-card>
    @else
        <form method="POST" action="{{ route('operations.attendance.bulk') }}">
            @csrf
            <input type="hidden" name="campaign_id" value="{{ $campaignId }}">
            <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">
            <input type="hidden" name="shift_number" value="{{ $shiftNumber }}">

            <x-card :title="__('attendance.grid_title')" :flush="true">
                <x-slot:actions>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="ti ti-device-floppy me-1"></i> {{ __('attendance.actions.save_all') }}
                    </button>
                </x-slot:actions>

                <div class="admin-table-scroll">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('attendance.table.member') }}</th>
                                <th>{{ __('attendance.table.role') }}</th>
                                <th>{{ __('attendance.table.status') }}</th>
                                <th>{{ __('attendance.table.check_in') }}</th>
                                <th>{{ __('attendance.table.check_out') }}</th>
                                <th>{{ __('attendance.fields.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $index => $member)
                            @php $record = $existing->get($member->id); @endphp
                            <input type="hidden" name="rows[{{ $index }}][member_id]" value="{{ $member->id }}">
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $member->full_name }}</div>
                                    <div class="text-muted small">{{ $member->mobile }}</div>
                                </td>
                                <td>{{ $member->memberRole?->name ?? '—' }}</td>
                                <td style="min-width: 160px;">
                                    <select name="rows[{{ $index }}][attendance_status_id]" class="form-select form-select-sm" required>
                                        @foreach($attendanceStatuses as $status)
                                            <option value="{{ $status->id }}" @selected($record?->attendance_status_id == $status->id)>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="min-width: 120px;">
                                    <input type="time" name="rows[{{ $index }}][check_in]" class="form-control form-control-sm"
                                           value="{{ $record?->checkInLabel() !== '—' ? $record?->checkInLabel() : '' }}">
                                </td>
                                <td style="min-width: 120px;">
                                    <input type="time" name="rows[{{ $index }}][check_out]" class="form-control form-control-sm"
                                           value="{{ $record?->checkOutLabel() !== '—' ? $record?->checkOutLabel() : '' }}">
                                </td>
                                <td style="min-width: 180px;">
                                    <input type="text" name="rows[{{ $index }}][notes]" class="form-control form-control-sm"
                                           value="{{ $record?->notes }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top bg-light text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ __('attendance.actions.save_all') }}
                    </button>
                </div>
            </x-card>
        </form>
    @endif
@else
    <x-card>
        <div class="text-center text-muted py-5">{{ __('attendance.messages.select_campaign') }}</div>
    </x-card>
@endif
@endsection
