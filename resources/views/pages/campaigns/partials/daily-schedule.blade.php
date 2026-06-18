@php
    $defaultOpenDay = collect($dailySchedule)->search(fn (array $day): bool => $day['is_today'])
        ?? collect($dailySchedule)->search(fn (array $day): bool => $day['counts']['attendance'] > 0 || $day['counts']['assigned'] > 0)
        ?? 0;
@endphp

<x-card :title="__('campaigns.daily_schedule.title')" class="mb-0">
    <p class="text-muted mb-4" style="font-size: 0.875rem;">{{ __('campaigns.daily_schedule.subtitle', ['days' => $campaign->campaignDaysCount()]) }}</p>

    @if(empty($dailySchedule))
        <div class="text-center text-muted py-5">{{ __('campaigns.daily_schedule.empty') }}</div>
    @else
        <div class="accordion campaign-day-accordion" id="campaignDailySchedule">
            @foreach($dailySchedule as $index => $day)
                @php
                    $attendanceByMember = $day['attendances']->keyBy('member_id');
                    $collapseId = 'campaign-day-'.$day['day_number'];
                    $isOpen = $index === $defaultOpenDay;
                @endphp
                <div class="accordion-item campaign-day-accordion__item @if($day['is_today']) campaign-day-accordion__item--today @endif">
                    <h2 class="accordion-header" id="heading-{{ $collapseId }}">
                        <button
                            class="accordion-button @unless($isOpen) collapsed @endunless"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}"
                            aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                            aria-controls="{{ $collapseId }}"
                        >
                            <span class="campaign-day-accordion__heading">
                                <span class="campaign-day-accordion__day">
                                    {{ __('campaigns.daily_schedule.day', ['number' => $day['day_number']]) }}
                                </span>
                                <span class="campaign-day-accordion__date">{{ $day['date']->format('Y-m-d') }}</span>
                                @if($day['is_today'])
                                    <span class="badge bg-primary-subtle text-primary">{{ __('campaigns.daily_schedule.today') }}</span>
                                @endif
                            </span>
                            <span class="campaign-day-accordion__badges">
                                <span class="badge bg-secondary-subtle text-secondary">
                                    <i class="ti ti-users me-1"></i>{{ $day['counts']['assigned'] }}
                                </span>
                                <span class="badge bg-success-subtle text-success">
                                    <i class="ti ti-clipboard-check me-1"></i>{{ $day['counts']['present'] }}/{{ $day['counts']['attendance'] }}
                                </span>
                                @if($day['counts']['patients'] > 0)
                                    <span class="badge bg-info-subtle text-info">
                                        <i class="ti ti-user-heart me-1"></i>{{ $day['counts']['patients'] }}
                                    </span>
                                @endif
                                @if($day['counts']['trips'] > 0)
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ti ti-bus me-1"></i>{{ $day['counts']['trips'] }}
                                    </span>
                                @endif
                                @if($day['counts']['activities'] > 0)
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="ti ti-activity me-1"></i>{{ $day['counts']['activities'] }}
                                    </span>
                                @endif
                            </span>
                        </button>
                    </h2>
                    <div id="{{ $collapseId }}" class="accordion-collapse collapse @if($isOpen) show @endif" aria-labelledby="heading-{{ $collapseId }}" data-bs-parent="#campaignDailySchedule">
                        <div class="accordion-body">
                            @if(
                                $day['counts']['assigned'] === 0
                                && $day['counts']['attendance'] === 0
                                && $day['counts']['patients'] === 0
                                && $day['counts']['trips'] === 0
                                && $day['counts']['activities'] === 0
                            )
                                <div class="text-muted py-3">{{ __('campaigns.daily_schedule.no_data') }}</div>
                            @else
                                <div class="row g-4">
                                    <div class="col-12">
                                        <h6 class="campaign-day-section__title">
                                            <i class="ti ti-stethoscope me-1"></i>{{ __('campaigns.daily_schedule.assigned_team') }}
                                        </h6>
                                        @if($day['assigned_members']->isEmpty())
                                            <p class="text-muted mb-0">{{ __('campaigns.daily_schedule.no_team') }}</p>
                                        @else
                                            <div class="table-responsive admin-table-scroll">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>{{ __('attendance.table.member') }}</th>
                                                            <th>{{ __('attendance.table.role') }}</th>
                                                            <th>{{ __('members.fields.assigned_role') }}</th>
                                                            <th>{{ __('attendance.table.shift') }}</th>
                                                            <th>{{ __('attendance.table.status') }}</th>
                                                            <th>{{ __('attendance.table.check_in') }}</th>
                                                            <th>{{ __('attendance.table.check_out') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($day['assigned_members'] as $assignment)
                                                            @php $attendance = $attendanceByMember->get($assignment->member_id); @endphp
                                                            <tr>
                                                                <td>
                                                                    <div class="fw-medium">{{ $assignment->member?->full_name ?? '—' }}</div>
                                                                    <div class="text-muted small">{{ $assignment->member?->specialty?->name }}</div>
                                                                </td>
                                                                <td>{{ $assignment->member?->memberRole?->name ?? '—' }}</td>
                                                                <td>{{ $assignment->assigned_role ?: '—' }}</td>
                                                                <td>{{ $attendance?->shift_number ?? '—' }}</td>
                                                                <td>
                                                                    @if($attendance?->attendanceStatus)
                                                                        <span class="badge border" style="background-color: {{ $attendance->attendanceStatus->color }}20; color: {{ $attendance->attendanceStatus->color }};">
                                                                            {{ $attendance->attendanceStatus->name }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">{{ __('campaigns.daily_schedule.not_recorded') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $attendance?->checkInLabel() ?? '—' }}</td>
                                                                <td>{{ $attendance?->checkOutLabel() ?? '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                    @can('viewAny', \App\Models\Patient::class)
                                        @if($day['patients']->isNotEmpty())
                                            <div class="col-md-6">
                                                <h6 class="campaign-day-section__title">
                                                    <i class="ti ti-user-heart me-1"></i>{{ __('campaigns.daily_schedule.patients') }}
                                                </h6>
                                                <ul class="list-unstyled campaign-day-list mb-0">
                                                    @foreach($day['patients'] as $patient)
                                                        <li class="campaign-day-list__item">
                                                            @can('view', $patient)
                                                                <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none">{{ $patient->patient_name }}</a>
                                                            @else
                                                                {{ $patient->patient_name }}
                                                            @endcan
                                                            <span class="text-muted small">{{ $patient->file_number }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @endcan

                                    @can('viewAny', \App\Models\TransportationTrip::class)
                                        @if($day['trips']->isNotEmpty())
                                            <div class="col-md-6">
                                                <h6 class="campaign-day-section__title">
                                                    <i class="ti ti-bus me-1"></i>{{ __('campaigns.daily_schedule.trips') }}
                                                </h6>
                                                <ul class="list-unstyled campaign-day-list mb-0">
                                                    @foreach($day['trips'] as $trip)
                                                        <li class="campaign-day-list__item">
                                                            @can('view', $trip)
                                                                <a href="{{ route('operations.transportation.show', $trip) }}" class="text-decoration-none"><code>{{ $trip->trip_code }}</code></a>
                                                            @else
                                                                <code>{{ $trip->trip_code }}</code>
                                                            @endcan
                                                            <span class="text-muted small">{{ $trip->fromLocation?->name }} → {{ $trip->toLocation?->name }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @endcan

                                    @can('viewAny', \App\Models\Activity::class)
                                        @if($day['activities']->isNotEmpty())
                                            <div class="col-md-6">
                                                <h6 class="campaign-day-section__title">
                                                    <i class="ti ti-activity me-1"></i>{{ __('campaigns.daily_schedule.activities') }}
                                                </h6>
                                                <ul class="list-unstyled campaign-day-list mb-0">
                                                    @foreach($day['activities'] as $activity)
                                                        <li class="campaign-day-list__item">
                                                            @can('view', $activity)
                                                                <a href="{{ route('operations.activities.show', $activity) }}" class="text-decoration-none">{{ $activity->title }}</a>
                                                            @else
                                                                {{ $activity->title }}
                                                            @endcan
                                                            <span class="text-muted small">{{ $activity->activityType?->name }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @endcan

                                    @if($day['attendances']->isNotEmpty())
                                        @php
                                            $extraAttendances = $day['attendances']->filter(
                                                fn ($attendance) => ! $day['assigned_members']->contains('member_id', $attendance->member_id)
                                            );
                                        @endphp
                                        @if($extraAttendances->isNotEmpty())
                                            <div class="col-12">
                                                <h6 class="campaign-day-section__title">
                                                    <i class="ti ti-clipboard-list me-1"></i>{{ __('campaigns.daily_schedule.other_attendance') }}
                                                </h6>
                                                <div class="table-responsive admin-table-scroll">
                                                    <table class="table table-sm table-hover align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>{{ __('attendance.table.member') }}</th>
                                                                <th>{{ __('attendance.table.shift') }}</th>
                                                                <th>{{ __('attendance.table.status') }}</th>
                                                                <th>{{ __('attendance.table.check_in') }}</th>
                                                                <th>{{ __('attendance.table.check_out') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($extraAttendances as $attendance)
                                                                <tr>
                                                                    <td>{{ $attendance->member?->full_name }}</td>
                                                                    <td>{{ $attendance->shift_number }}</td>
                                                                    <td>{{ $attendance->attendanceStatus?->name ?? '—' }}</td>
                                                                    <td>{{ $attendance->checkInLabel() }}</td>
                                                                    <td>{{ $attendance->checkOutLabel() }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif

                            @can('create', \App\Models\Attendance::class)
                                <div class="mt-3 pt-3 border-top text-end">
                                    <a href="{{ route('operations.attendance.quick', ['campaign_id' => $campaign->id, 'attendance_date' => $day['date_key']]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-clipboard-check me-1"></i>{{ __('campaigns.daily_schedule.record_attendance') }}
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-card>
