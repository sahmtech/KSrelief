@php
    $s = $dashboard['stats'];
    $v = $dashboard['visibleSections'];
    $showFilters = (! isset($filter) || ! $filter?->campaignId) && ($v['filters'] || $v['filters_dates_only']);
@endphp

@if($v['quick_actions'] && count($dashboard['quickActions']))
<x-quick-actions-panel :actions="$dashboard['quickActions']" />
@endif

@if($showFilters)
<form method="GET" action="{{ route('dashboard') }}" class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            @if($v['filters'])
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('dashboard.filters.campaign') }}</label>
                <select name="campaign_id" class="form-select form-select-sm">
                    <option value="">{{ __('dashboard.filters.all_campaigns') }}</option>
                    @foreach($dashboard['filterOptions']['campaigns'] as $c)
                    <option value="{{ $c->id }}" @selected($filter->campaignId == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('dashboard.filters.country') }}</label>
                <select name="country_id" class="form-select form-select-sm">
                    <option value="">{{ __('dashboard.filters.all') }}</option>
                    @foreach($dashboard['filterOptions']['countries'] as $country)
                    <option value="{{ $country->id }}" @selected($filter->countryId == $country->id)>{{ $country->localizedName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('dashboard.filters.specialty') }}</label>
                <select name="specialty_id" class="form-select form-select-sm">
                    <option value="">{{ __('dashboard.filters.all') }}</option>
                    @foreach($dashboard['filterOptions']['specialties'] as $specialty)
                    <option value="{{ $specialty->id }}" @selected($filter->specialtyId == $specialty->id)>{{ $specialty->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('dashboard.filters.date_from') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filter->dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('dashboard.filters.date_to') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filter->dateTo }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="ti ti-filter"></i></button>
            </div>
        </div>
    </div>
</form>
@endif

@if($dashboard['isEmpty'] ?? false)
<x-card class="mb-4">
    <div class="text-center text-muted py-5">
        <i class="ti ti-dashboard d-block mb-3 fs-1 opacity-50"></i>
        <h5 class="mb-2">{{ __('dashboard.empty.title') }}</h5>
        <p class="mb-0">{{ __('dashboard.empty.subtitle') }}</p>
    </div>
</x-card>
@endif

@if($v['overview'] && count($dashboard['overviewCards']))
<div class="row g-3 mb-4">
    @foreach($dashboard['overviewCards'] as $card)
    <div class="col-sm-6 col-xl-3">
        <x-kpi-card :label="$card['label']" :value="$card['value']" :icon="'ti '.$card['icon']" :variant="$card['variant']" />
    </div>
    @endforeach
</div>
@endif

@if($showFilters && $v['campaigns'])
<x-statistics-section :title="__('dashboard.sections.campaigns')">
    <div class="col-6 col-md-3"><x-kpi-card :label="__('dashboard.kpi.total_campaigns')" :value="(string)$s['campaigns']['total']" icon="ti ti-flag" variant="primary" /></div>
    <div class="col-6 col-md-3"><x-kpi-card :label="__('dashboard.kpi.active_campaigns')" :value="(string)$s['campaigns']['active']" icon="ti ti-flag-2" variant="success" /></div>
    <div class="col-6 col-md-3"><x-kpi-card :label="__('dashboard.kpi.completed_campaigns')" :value="(string)$s['campaigns']['completed']" icon="ti ti-circle-check" variant="secondary" /></div>
    <div class="col-6 col-md-3"><x-kpi-card :label="__('dashboard.kpi.cancelled_campaigns')" :value="(string)$s['campaigns']['cancelled']" icon="ti ti-ban" variant="warning" /></div>
</x-statistics-section>
@endif

@if($v['patients'])
<x-statistics-section :title="__('dashboard.sections.patients')">
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.kpi.total_patients')" :value="(string)$s['patients']['total']" icon="ti ti-users" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.kpi.accepted')" :value="(string)$s['patients']['accepted']" icon="ti ti-check" variant="success" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.kpi.rejected')" :value="(string)$s['patients']['rejected']" icon="ti ti-x" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.kpi.postponed')" :value="(string)$s['patients']['postponed']" icon="ti ti-clock-pause" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.kpi.admitted')" :value="(string)$s['patients']['admitted']" icon="ti ti-bed" variant="secondary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.kpi.completed_patients')" :value="(string)$s['patients']['completed']" icon="ti ti-circle-check" variant="success" /></div>
</x-statistics-section>
@endif

@if($v['workflow'])
<x-statistics-section :title="__('dashboard.sections.workflow')">
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.workflow.waiting_admission')" :value="(string)$s['workflow']['waiting_admission']" icon="ti ti-door-enter" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.workflow.waiting_operation')" :value="(string)$s['workflow']['waiting_operation']" icon="ti ti-stethoscope" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.workflow.waiting_activation')" :value="(string)$s['workflow']['waiting_activation']" icon="ti ti-activity" variant="secondary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.workflow.in_rehabilitation')" :value="(string)$s['workflow']['in_rehabilitation']" icon="ti ti-walk" variant="success" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.workflow.completion_rate')" :value="$s['workflow']['completion_rate'].'%'" icon="ti ti-progress" variant="primary" /></div>
</x-statistics-section>
@if($s['workflow']['patients_by_stage']->isNotEmpty())
<div class="row g-2 mb-4">
    @foreach($s['workflow']['patients_by_stage'] as $stat)
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-2 text-center">
                <div class="fw-bold fs-5" style="color: {{ $stat['stage']->color ?? '#6B7280' }};">{{ $stat['count'] }}</div>
                <div class="small text-truncate" style="font-size: 0.7rem; color: {{ $stat['stage']->color ?? '#6B7280' }};">{{ $stat['stage']->name }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endif

@if($v['members'])
<x-statistics-section :title="__('dashboard.sections.members')">
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.members.total')" :value="(string)$s['members']['total']" icon="ti ti-users" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.members.doctors')" :value="(string)$s['members']['doctors']" icon="ti ti-stethoscope" variant="success" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.members.specialists')" :value="(string)$s['members']['specialists']" icon="ti ti-user-star" variant="secondary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.members.coordinators')" :value="(string)$s['members']['coordinators']" icon="ti ti-users-group" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.members.assigned')" :value="(string)$s['members']['assigned']" icon="ti ti-link" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.members.available')" :value="(string)$s['members']['available']" icon="ti ti-user-check" variant="success" /></div>
</x-statistics-section>
@endif

@if($v['attendance'])
<x-statistics-section :title="__('dashboard.attendance.title')">
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.attendance.present_today')" :value="(string)$s['attendance']['present_today']" icon="ti ti-circle-check" variant="success" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.attendance.late_today')" :value="(string)$s['attendance']['late_today']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.attendance.absent_today')" :value="(string)$s['attendance']['absent_today']" icon="ti ti-x" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.attendance.leave_today')" :value="(string)$s['attendance']['leave_today']" icon="ti ti-calendar-off" variant="secondary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.attendance.attendance_rate')" :value="$s['attendance']['attendance_rate'].'%'" icon="ti ti-percentage" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.attendance.monthly_rate')" :value="$s['attendance']['monthly_attendance_rate'].'%'" icon="ti ti-chart-line" variant="primary" /></div>
</x-statistics-section>
@endif

@if($v['transportation'])
<x-statistics-section :title="__('dashboard.transportation.title')">
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.transportation.today_trips')" :value="(string)$s['transportation']['today_trips']" icon="ti ti-bus" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.transportation.upcoming_trips')" :value="(string)$s['transportation']['upcoming_trips']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.transportation.completed_trips')" :value="(string)$s['transportation']['completed_trips']" icon="ti ti-circle-check" variant="success" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.transportation.cancelled_trips')" :value="(string)$s['transportation']['cancelled_trips']" icon="ti ti-ban" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.transportation.patients_transported')" :value="(string)$s['transportation']['patients_transported']" icon="ti ti-user-heart" variant="secondary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.transportation.members_transported')" :value="(string)$s['transportation']['members_transported']" icon="ti ti-stethoscope" variant="secondary" /></div>
</x-statistics-section>
@endif

@if($v['activities'])
<x-statistics-section :title="__('dashboard.activities.title')">
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.activities.today')" :value="(string)$s['activities']['today']" icon="ti ti-activity" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.activities.upcoming')" :value="(string)$s['activities']['upcoming']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.activities.completed_today')" :value="(string)$s['activities']['completed_today']" icon="ti ti-circle-check" variant="success" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.activities.cancelled')" :value="(string)$s['activities']['cancelled']" icon="ti ti-ban" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.activities.completion_rate')" :value="$s['activities']['completion_rate'].'%'" icon="ti ti-chart-pie" variant="primary" /></div>
</x-statistics-section>
@endif

@if($v['imports'])
<x-statistics-section :title="__('dashboard.sections.imports')">
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.imports.total')" :value="(string)$s['imports']['total']" icon="ti ti-file-import" variant="primary" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.imports.pending')" :value="(string)$s['imports']['pending_review']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.imports.completed')" :value="(string)$s['imports']['completed']" icon="ti ti-circle-check" variant="success" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.imports.failed')" :value="(string)$s['imports']['failed']" icon="ti ti-alert-circle" variant="warning" /></div>
    <div class="col-6 col-md-4 col-lg"><x-kpi-card :label="__('dashboard.imports.patients_imported')" :value="(string)$s['imports']['patients_imported']" icon="ti ti-users" variant="secondary" /></div>
</x-statistics-section>
@endif

@if($v['charts'] && ! empty($dashboard['chartRows']))
@foreach($dashboard['chartRows'] as $chartRow)
<div class="row g-3 mb-4">
    @foreach($chartRow as $panel)
    <div class="{{ $panel['class'] }}">
        <x-chart-card :title="$panel['chart']['title']" :config="$panel['chart']['config']" :height="$panel['height']" />
    </div>
    @endforeach
</div>
@endforeach
@endif

@php
    $upcomingItems = collect();
    if ($v['upcoming_events']) {
        $dashboard['upcoming']['activities']->each(fn ($a) => $upcomingItems->push([
            'title' => $a->title,
            'meta' => $a->campaign?->name,
            'date' => $a->activity_date?->format('Y-m-d'),
            'url' => route('operations.activities.show', $a),
            'icon' => 'ti-activity',
        ]));
        $dashboard['upcoming']['trips']->each(fn ($t) => $upcomingItems->push([
            'title' => $t->trip_code,
            'meta' => $t->campaign?->name,
            'date' => $t->trip_date?->format('Y-m-d'),
            'url' => route('operations.transportation.show', $t),
            'icon' => 'ti-bus',
        ]));
        $dashboard['upcoming']['campaigns']->each(fn ($c) => $upcomingItems->push([
            'title' => $c->name,
            'meta' => __('dashboard.upcoming.campaign'),
            'date' => $c->start_date?->format('Y-m-d'),
            'url' => route('campaigns.show', $c),
            'icon' => 'ti-flag',
        ]));
    }
@endphp

@if($v['recent_activity'] || $v['upcoming_events'])
<div class="row g-3 mb-4">
    @if($v['recent_activity'])
    <div class="{{ $v['upcoming_events'] ? 'col-lg-7' : 'col-12' }}">
        <x-recent-activity-card :title="__('dashboard.recent_activity.title')" :subtitle="__('dashboard.recent_activity.subtitle')" :items="$dashboard['recentFeed']->all()" />
    </div>
    @endif
    @if($v['upcoming_events'])
    <div class="{{ $v['recent_activity'] ? 'col-lg-5' : 'col-12' }}">
        <x-timeline-card
            :title="__('dashboard.upcoming.title')"
            :subtitle="__('dashboard.upcoming.subtitle')"
            :items="$upcomingItems->take(8)->all()"
        />
    </div>
    @endif
</div>
@endif

@if($v['audit_placeholder'])
<x-card :title="__('dashboard.audit.title')" class="mb-4">
    <div class="text-center text-muted py-3"><i class="ti ti-shield-check d-block mb-2 fs-3 opacity-50"></i>{{ __('dashboard.audit.placeholder') }}</div>
</x-card>
@endif
