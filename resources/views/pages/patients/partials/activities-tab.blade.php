<div class="row g-3 mb-4">
    <div class="col-md-4"><x-stats-card :label="__('activities.stats.total')" :value="$activityStats['total']" icon="ti ti-activity" variant="primary" /></div>
    <div class="col-md-4"><x-stats-card :label="__('activities.stats.upcoming')" :value="$activityStats['upcoming']" icon="ti ti-clock" variant="warning" /></div>
    <div class="col-md-4"><x-stats-card :label="__('activities.stats.completed')" :value="$activityStats['completed']" icon="ti ti-circle-check" variant="success" /></div>
</div>
<x-card :title="__('activities.patient.history')" :flush="true">
    @if($patientActivities->isEmpty())<div class="text-center text-muted py-4">{{ __('activities.messages.no_activities') }}</div>
    @else<table class="table table-hover mb-0"><thead><tr><th>{{ __('activities.table.title') }}</th><th>{{ __('activities.table.date') }}</th><th>{{ __('activities.table.type') }}</th><th>{{ __('activities.table.status') }}</th></tr></thead><tbody>
        @foreach($patientActivities as $act)<tr><td><a href="{{ route('operations.activities.show',$act) }}">{{ $act->title }}</a></td><td>{{ $act->activity_date->format('Y-m-d') }}</td><td>{{ $act->activityType?->name }}</td><td>{{ $act->statusLabel() }}</td></tr>@endforeach
    </tbody></table>@endif
</x-card>
