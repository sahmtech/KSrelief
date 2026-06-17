<div class="row g-3 mb-4">
    <div class="col-md-4">
        <x-stats-card :label="__('transportation.stats.total')" :value="$transportStats['total']" icon="ti ti-route" variant="primary" />
    </div>
    <div class="col-md-4">
        <x-stats-card :label="__('transportation.member.upcoming')" :value="$transportStats['upcoming']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-md-4">
        <x-stats-card :label="__('transportation.stats.completed')" :value="$transportStats['completed']" icon="ti ti-circle-check" variant="success" />
    </div>
</div>

<x-card :title="__('transportation.member.assigned_trips')" :flush="true">
    @if($memberTrips->isEmpty())
        <div class="text-center text-muted py-4">{{ __('transportation.messages.no_trips') }}</div>
    @else
        <div class="table-responsive admin-table-scroll">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('transportation.table.trip_code') }}</th>
                        <th>{{ __('transportation.table.campaign') }}</th>
                        <th>{{ __('transportation.table.trip_date') }}</th>
                        <th>{{ __('transportation.table.from') }}</th>
                        <th>{{ __('transportation.table.to') }}</th>
                        <th>{{ __('transportation.table.status') }}</th>
                        <th class="text-end">{{ __('transportation.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($memberTrips as $trip)
                    <tr>
                        <td><code>{{ $trip->trip_code }}</code></td>
                        <td>{{ $trip->campaign?->name }}</td>
                        <td>{{ $trip->trip_date->format('Y-m-d') }}</td>
                        <td>{{ $trip->fromLocation?->name }}</td>
                        <td>{{ $trip->toLocation?->name }}</td>
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
