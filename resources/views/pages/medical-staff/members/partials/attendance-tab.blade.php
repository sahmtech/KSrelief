<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('attendance.stats.total')" :value="$attendanceStats['total']" icon="ti ti-list" variant="secondary" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('attendance.stats.present')" :value="$attendanceStats['present']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('attendance.stats.late')" :value="$attendanceStats['late']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('attendance.member.rate')" :value="$attendanceStats['attendance_rate'] . '%'" icon="ti ti-percentage" variant="primary" />
    </div>
</div>

<x-card :title="__('attendance.member.recent')" :flush="true">
    <x-slot:actions>
        @can('viewAny', \App\Models\Attendance::class)
            <a href="{{ route('operations.attendance.index', ['search' => $member->full_name]) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-list me-1"></i> {{ __('common.view_all') }}
            </a>
        @endcan
    </x-slot:actions>

    @if($recentAttendances->isEmpty())
        <div class="text-center text-muted py-4">{{ __('workflow.no_records') }}</div>
    @else
        <div class="table-responsive admin-table-scroll">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('attendance.table.date') }}</th>
                        <th>{{ __('attendance.table.campaign') }}</th>
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
                            <td>{{ $attendance->campaign?->name }}</td>
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
