<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('transportation.stats.total')" :value="$transportStats['total']" icon="ti ti-route" variant="primary" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('transportation.stats.upcoming')" :value="$transportStats['upcoming']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('transportation.stats.completed')" :value="$transportStats['completed']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('transportation.stats.passengers_transported')" :value="$transportStats['passengers_transported']" icon="ti ti-users" variant="secondary" />
    </div>
</div>

<x-card :title="__('transportation.campaign.recent_trips')" :flush="true">
    <x-slot:actions>
        @can('create', \App\Models\TransportationTrip::class)
            <a href="{{ route('operations.transportation.create', ['campaign_id' => $campaign->id]) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> {{ __('transportation.actions.create') }}
            </a>
        @endcan
        @can('viewAny', \App\Models\TransportationTrip::class)
            <a href="{{ route('operations.transportation.index', ['campaign_id' => $campaign->id]) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-list me-1"></i> {{ __('common.view_all') }}
            </a>
        @endcan
    </x-slot:actions>

    @if($recentTrips->isEmpty())
        <div class="text-center text-muted py-4">{{ __('transportation.messages.no_trips') }}</div>
    @else
        <div class="table-responsive admin-table-scroll">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('transportation.table.trip_code') }}</th>
                        <th>{{ __('transportation.table.trip_date') }}</th>
                        <th>{{ __('transportation.table.from') }}</th>
                        <th>{{ __('transportation.table.to') }}</th>
                        <th>{{ __('transportation.table.passengers') }}</th>
                        <th>{{ __('transportation.table.status') }}</th>
                        <th class="text-end">{{ __('transportation.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTrips as $trip)
                    <tr>
                        <td><code>{{ $trip->trip_code }}</code></td>
                        <td>{{ $trip->trip_date->format('Y-m-d') }}</td>
                        <td>{{ $trip->fromLocation?->name }}</td>
                        <td>{{ $trip->toLocation?->name }}</td>
                        <td>{{ $trip->passengers_count }}</td>
                        <td><span class="badge-status {{ $trip->statusBadgeClass() }}">{{ $trip->statusLabel() }}</span></td>
                        <td class="text-end">
                            @can('view', $trip)
                            <a href="{{ route('operations.transportation.show', $trip) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye"></i></a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
