<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><x-stats-card :label="__('activities.stats.total')" :value="$activityStats['total']" icon="ti ti-activity" variant="primary" /></div>
    <div class="col-6 col-md-3"><x-stats-card :label="__('activities.stats.upcoming')" :value="$activityStats['upcoming']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-6 col-md-3"><x-stats-card :label="__('activities.stats.completed')" :value="$activityStats['completed']" icon="ti ti-circle-check" variant="success" /></div>
    <div class="col-6 col-md-3"><x-stats-card :label="__('activities.stats.participants')" :value="$activityStats['participants']" icon="ti ti-users" variant="secondary" /></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <x-card :title="__('activities.campaign.upcoming')" :flush="true">
            @if($upcomingActivities->isEmpty())<div class="text-center text-muted py-4">{{ __('activities.messages.no_activities') }}</div>
            @else<table class="table table-hover mb-0"><thead><tr><th>{{ __('activities.table.title') }}</th><th>{{ __('activities.table.date') }}</th><th>{{ __('activities.table.status') }}</th></tr></thead><tbody>
                @foreach($upcomingActivities as $act)<tr><td><a href="{{ route('operations.activities.show',$act) }}">{{ $act->title }}</a></td><td>{{ $act->activity_date->format('Y-m-d') }}</td><td>{{ $act->statusLabel() }}</td></tr>@endforeach
            </tbody></table>@endif
        </x-card>
    </div>
    <div class="col-lg-5">
        <x-card :title="__('activities.actions.calendar')">
            <p class="text-muted small mb-3">{{ __('activities.calendar_subtitle') }}</p>
            <a href="{{ route('operations.activities.calendar',['campaign_id'=>$campaign->id]) }}" class="btn btn-primary btn-sm w-100"><i class="ti ti-calendar me-1"></i>{{ __('activities.actions.calendar') }}</a>
            @can('create', \App\Models\Activity::class)
            <a href="{{ route('operations.activities.create',['campaign_id'=>$campaign->id]) }}" class="btn btn-outline-primary btn-sm w-100 mt-2"><i class="ti ti-plus me-1"></i>{{ __('activities.actions.create') }}</a>
            @endcan
        </x-card>
    </div>
</div>

<x-card :title="__('activities.campaign.recent')" :flush="true">
    <x-slot:actions><a href="{{ route('operations.activities.index',['campaign_id'=>$campaign->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('common.view_all') }}</a></x-slot:actions>
    @if($recentActivities->isEmpty())<div class="text-center text-muted py-4">{{ __('activities.messages.no_activities') }}</div>
    @else<table class="table table-hover mb-0"><thead><tr><th>{{ __('activities.table.title') }}</th><th>{{ __('activities.table.type') }}</th><th>{{ __('activities.table.date') }}</th><th>{{ __('activities.table.participants') }}</th></tr></thead><tbody>
        @foreach($recentActivities as $act)<tr><td><a href="{{ route('operations.activities.show',$act) }}">{{ $act->title }}</a></td><td>{{ $act->activityType?->name }}</td><td>{{ $act->activity_date->format('Y-m-d') }}</td><td>{{ $act->participants_count }}</td></tr>@endforeach
    </tbody></table>@endif
</x-card>
