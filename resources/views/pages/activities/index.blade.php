@extends('layouts.admin')

@section('title', __('activities.title'))

@section('content')
<x-page-header :title="__('activities.title')" :subtitle="__('activities.subtitle')"
    :breadcrumbs="[['label' => __('menu.operations')], ['label' => __('activities.title')]]">
    <a href="{{ route('operations.activities.calendar') }}" class="btn btn-outline-primary btn-sm me-1">
        <i class="ti ti-calendar me-1"></i> {{ __('activities.actions.calendar') }}
    </a>
    @can('create', \App\Models\Activity::class)
    <a href="{{ route('operations.activities.create') }}" class="btn btn-primary btn-sm">
        <i class="ti ti-plus me-1"></i> {{ __('activities.actions.create') }}
    </a>
    @endcan
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl"><x-stats-card :label="__('activities.stats.total')" :value="$stats['total']" icon="ti ti-activity" variant="primary" /></div>
    <div class="col-6 col-md-4 col-xl"><x-stats-card :label="__('activities.stats.today')" :value="$stats['today']" icon="ti ti-calendar-event" variant="secondary" /></div>
    <div class="col-6 col-md-4 col-xl"><x-stats-card :label="__('activities.stats.upcoming')" :value="$stats['upcoming']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-6 col-md-4 col-xl"><x-stats-card :label="__('activities.stats.completed')" :value="$stats['completed']" icon="ti ti-circle-check" variant="success" /></div>
    <div class="col-6 col-md-4 col-xl"><x-stats-card :label="__('activities.stats.completion_rate')" :value="$stats['completion_rate'].'%'" icon="ti ti-percentage" variant="primary" /></div>
</div>

<x-card :title="__('activities.filters.search')" :compact="true" class="mb-3">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label small">{{ __('activities.filters.search') }}</label>
            <input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] }}" placeholder="{{ __('activities.filters.search_placeholder') }}"></div>
        <div class="col-md-3"><label class="form-label small">{{ __('activities.filters.campaign') }}</label>
            <select name="campaign_id" class="form-select form-select-sm"><option value="">{{ __('activities.filters.all_campaigns') }}</option>
                @foreach($campaigns as $c)<option value="{{ $c->id }}" @selected((string)$filters['campaign_id']===(string)$c->id)>{{ $c->name }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label small">{{ __('activities.filters.type') }}</label>
            <select name="activity_type_id" class="form-select form-select-sm"><option value="">{{ __('activities.filters.all_types') }}</option>
                @foreach($activityTypes as $t)<option value="{{ $t->id }}" @selected((string)$filters['activity_type_id']===(string)$t->id)>{{ $t->name }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label small">{{ __('activities.filters.date_from') }}</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}"></div>
        <div class="col-md-2"><label class="form-label small">{{ __('activities.filters.date_to') }}</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}"></div>
        <div class="col-md-2"><label class="form-label small">{{ __('activities.filters.status') }}</label>
            <select name="status" class="form-select form-select-sm"><option value="">{{ __('activities.filters.all_statuses') }}</option>
                @foreach($activityStatuses as $s)<option value="{{ $s->value }}" @selected($filters['status']===$s->value)>{{ $s->label() }}</option>@endforeach
            </select></div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('activities.filters.apply') }}</button>
            <a href="{{ route('operations.activities.index') }}" class="btn btn-light btn-sm">{{ __('activities.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <x-datatable id="activitiesTable" :options="['order'=>[[2,'desc']],'columnDefs'=>[['targets'=>8,'orderable'=>false,'className'=>'text-end table-actions']]]">
        <x-slot:head><tr>
            <th>{{ __('activities.table.title') }}</th><th>{{ __('activities.table.campaign') }}</th><th>{{ __('activities.table.date') }}</th>
            <th>{{ __('activities.table.type') }}</th><th>{{ __('activities.table.start_time') }}</th><th>{{ __('activities.table.end_time') }}</th>
            <th>{{ __('activities.table.participants') }}</th><th>{{ __('activities.table.status') }}</th><th>{{ __('activities.table.actions') }}</th>
        </tr></x-slot:head>
        @forelse($activities as $activity)
        <tr>
            <td class="fw-medium">{{ $activity->title }}</td>
            <td>{{ $activity->campaign?->name }}</td>
            <td>{{ $activity->activity_date->format('Y-m-d') }}</td>
            <td><span class="badge border" style="background:{{ $activity->activityType?->color }}20;color:{{ $activity->activityType?->color }}">{{ $activity->activityType?->name }}</span></td>
            <td>{{ $activity->startTimeLabel() }}</td><td>{{ $activity->endTimeLabel() }}</td>
            <td>{{ $activity->participants_count }}</td>
            <td><span class="badge-status {{ $activity->statusBadgeClass() }}">{{ $activity->statusLabel() }}</span></td>
            <td class="text-end">
                <div class="dropdown" data-table-dropdown>
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @can('view',$activity)<li><a class="dropdown-item" href="{{ route('operations.activities.show',$activity) }}"><i class="ti ti-eye me-2"></i>{{ __('activities.actions.view') }}</a></li>@endcan
                        @can('update',$activity)<li><a class="dropdown-item" href="{{ route('operations.activities.edit',$activity) }}"><i class="ti ti-edit me-2"></i>{{ __('activities.actions.edit') }}</a></li>@endcan
                        @can('delete',$activity)<li><form method="POST" action="{{ route('operations.activities.destroy',$activity) }}">@csrf @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('activities.messages.confirm_delete') }}"><i class="ti ti-trash me-2"></i>{{ __('activities.actions.delete') }}</button></form></li>@endcan
                    </ul>
                </div>
            </td>
        </tr>
        @empty<tr><td colspan="9" class="text-center text-muted py-4">{{ __('activities.messages.no_activities') }}</td></tr>@endforelse
    </x-datatable>
</x-card>
@endsection
