<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.present')" :value="$attendanceStats['present']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.late')" :value="$attendanceStats['late']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.absent')" :value="$attendanceStats['absent']" icon="ti ti-circle-x" variant="danger" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('attendance.stats.attendance_rate')" :value="$attendanceStats['attendance_rate'] . '%'" icon="ti ti-percentage" variant="primary" />
    </div>
</div>

<x-card :title="__('attendance.campaign.daily_list')" :flush="true">
    <x-slot:actions>
        @can('create', \App\Models\Attendance::class)
            <a href="{{ route('operations.attendance.quick', ['campaign_id' => $campaign->id, 'attendance_date' => now()->toDateString()]) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-clipboard-check me-1"></i> {{ __('attendance.campaign.quick_link') }}
            </a>
        @endcan
        @can('viewAny', \App\Models\Attendance::class)
            <a href="{{ route('operations.attendance.index', ['campaign_id' => $campaign->id]) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-list me-1"></i> {{ __('common.view_all') }}
            </a>
        @endcan
    </x-slot:actions>

    @if($recentAttendances->isEmpty())
        <div class="text-center text-muted py-4">{{ __('attendance.messages.no_members') }}</div>
    @else
        <div class="table-responsive admin-table-scroll">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('attendance.table.date') }}</th>
                        <th>{{ __('attendance.table.member') }}</th>
                        <th>{{ __('attendance.table.shift') }}</th>
                        <th>{{ __('attendance.table.check_in') }}</th>
                        <th>{{ __('attendance.table.check_out') }}</th>
                        <th>{{ __('attendance.table.status') }}</th>
                        <th class="text-end">{{ __('attendance.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAttendances as $attendance)
                        <tr>
                            <td>{{ $attendance->attendance_date->format('Y-m-d') }}</td>
                            <td>
                                <div class="fw-medium">{{ $attendance->member?->full_name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $attendance->member?->memberRole?->name }}</div>
                            </td>
                            <td>{{ $attendance->shift_number }}</td>
                            <td>{{ $attendance->checkInLabel() }}</td>
                            <td>{{ $attendance->checkOutLabel() }}</td>
                            <td>
                                @if($attendance->attendanceStatus)
                                    <span class="badge border" style="background-color: {{ $attendance->attendanceStatus->color }}20; color: {{ $attendance->attendanceStatus->color }};">
                                        {{ $attendance->attendanceStatus->name }}
                                    </span>
                                @else — @endif
                            </td>
                            <td class="text-end">
                                @can('view', $attendance)
                                    <a href="{{ route('operations.attendance.show', $attendance) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
