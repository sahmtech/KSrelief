@extends('layouts.admin')

@section('title', $trip->trip_code)

@section('content')
<x-page-header
    :title="$trip->trip_code"
    :subtitle="$trip->campaign?->name . ' · ' . $trip->trip_date->format('Y-m-d')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('transportation.title'), 'url' => route('operations.transportation.index')],
        ['label' => $trip->trip_code],
    ]"
>
    @can('update', $trip)
        @if($trip->isEditable())
        <a href="{{ route('operations.transportation.edit', $trip) }}" class="btn btn-warning btn-sm">
            <i class="ti ti-edit me-1"></i> {{ __('transportation.actions.edit') }}
        </a>
        @endif
    @endcan
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge-status {{ $trip->statusBadgeClass() }}">{{ $trip->statusLabel() }}</span>
            <span class="badge bg-light text-dark border">{{ $trip->tripTypeLabel() }}</span>
            <span class="badge bg-light text-dark border">
                <i class="ti ti-map-pin me-1"></i>{{ $trip->fromLocation?->name }} → {{ $trip->toLocation?->name }}
            </span>
            <span class="badge bg-light text-dark border">
                <i class="ti ti-clock me-1"></i>{{ $trip->departureTimeLabel() }}
                @if($trip->arrivalTimeLabel() !== '—') — {{ $trip->arrivalTimeLabel() }} @endif
            </span>
        </div>
    </div>
    <div class="col-md-4 text-md-end">
        @can('changeStatus', $trip)
            @foreach($statusTransitions as $transition)
                @php
                    $action = match($transition) {
                        \App\Enums\TripStatus::InProgress => ['label' => __('transportation.actions.start_trip'), 'class' => 'btn-primary', 'confirm' => __('transportation.messages.confirm_start')],
                        \App\Enums\TripStatus::Completed => ['label' => __('transportation.actions.complete_trip'), 'class' => 'btn-success', 'confirm' => __('transportation.messages.confirm_complete')],
                        \App\Enums\TripStatus::Cancelled => ['label' => __('transportation.actions.cancel_trip'), 'class' => 'btn-outline-danger', 'confirm' => __('transportation.messages.confirm_cancel')],
                        default => null,
                    };
                @endphp
                @if($action)
                <form method="POST" action="{{ route('operations.transportation.status.update', $trip) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $transition->value }}">
                    <button type="submit" class="btn btn-sm {{ $action['class'] }} me-1" data-confirm="{{ $action['confirm'] }}">
                        {{ $action['label'] }}
                    </button>
                </form>
                @endif
            @endforeach
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <x-card :title="__('transportation.fields.trip_info')">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.fields.campaign') }}</div>
                    <div class="user-info-list__value">
                        <a href="{{ route('campaigns.show', $trip->campaign) }}" class="text-decoration-none">{{ $trip->campaign?->name }}</a>
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.table.trip_date') }}</div>
                    <div class="user-info-list__value">{{ $trip->trip_date->format('Y-m-d') }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.fields.from_location') }}</div>
                    <div class="user-info-list__value">{{ $trip->fromLocation?->name }} <span class="text-muted">({{ $trip->fromLocation?->type }})</span></div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.fields.to_location') }}</div>
                    <div class="user-info-list__value">{{ $trip->toLocation?->name }} <span class="text-muted">({{ $trip->toLocation?->type }})</span></div>
                </div>
                @if($trip->notes)
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.fields.notes') }}</div>
                    <div class="user-info-list__value">{{ $trip->notes }}</div>
                </div>
                @endif
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.table.created_by') }}</div>
                    <div class="user-info-list__value">{{ $trip->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </x-card>
    </div>
    <div class="col-lg-6">
        <x-card :title="__('transportation.fields.vehicle_info')">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.fields.vehicle_number') }}</div>
                    <div class="user-info-list__value">{{ $trip->vehicle_number ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.fields.driver_name') }}</div>
                    <div class="user-info-list__value">{{ $trip->driver_name ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('transportation.fields.capacity') }}</div>
                    <div class="user-info-list__value">
                        {{ $trip->passengers->count() }}@if($trip->capacity) / {{ $trip->capacity }}@endif
                    </div>
                </div>
            </div>
        </x-card>

        <x-card :title="__('transportation.fields.timeline')" class="mt-3">
            @if($trip->statusLogs->isEmpty())
                <div class="text-muted small">{{ __('workflow.no_records') }}</div>
            @else
                <div class="workflow-timeline">
                    @foreach($trip->statusLogs->sortByDesc('created_at') as $log)
                    <div class="workflow-timeline__item">
                        <div class="workflow-timeline__dot"></div>
                        <div class="workflow-timeline__content">
                            <div class="fw-medium">
                                {{ $log->old_status?->label() ?? '—' }} → {{ $log->new_status?->label() }}
                            </div>
                            <div class="text-muted small">
                                {{ $log->changedBy?->name }} · {{ $log->created_at?->format('Y-m-d H:i') }}
                            </div>
                            @if($log->notes)<div class="small mt-1">{{ $log->notes }}</div>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    <div class="col-12">
        <x-card :title="__('transportation.passengers_title')" :flush="true">
            <x-slot:actions>
                @can('managePassengers', $trip)
                    @if($trip->isEditable())
                        @if($trip->trip_type !== \App\Enums\TripType::MemberTransport)
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                            <i class="ti ti-user-heart me-1"></i> {{ __('transportation.actions.add_patient') }}
                        </button>
                        @endif
                        @if($trip->trip_type !== \App\Enums\TripType::PatientTransport)
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                            <i class="ti ti-stethoscope me-1"></i> {{ __('transportation.actions.add_member') }}
                        </button>
                        @endif
                    @endif
                @endcan
            </x-slot:actions>

            @if($trip->passengers->isEmpty())
                <div class="text-center text-muted py-4">{{ __('transportation.messages.no_passengers') }}</div>
            @else
                <div class="table-responsive admin-table-scroll">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('transportation.table.passenger') }}</th>
                                <th>{{ __('transportation.table.passenger_type') }}</th>
                                <th>{{ __('transportation.fields.notes') }}</th>
                                <th class="text-end">{{ __('transportation.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trip->passengers as $passenger)
                            <tr>
                                <td>
                                    @if($passenger->passenger_type === \App\Enums\PassengerType::Member)
                                        <a href="{{ route('medical-staff.members.show', $passenger->member) }}" class="fw-medium text-decoration-none">{{ $passenger->member?->full_name }}</a>
                                        <div class="text-muted small">{{ $passenger->member?->memberRole?->name }}</div>
                                    @else
                                        <a href="{{ route('patients.show', $passenger->patient) }}" class="fw-medium text-decoration-none">{{ $passenger->patient?->patient_name }}</a>
                                        <div class="text-muted small"><code>{{ $passenger->patient?->file_number }}</code></div>
                                    @endif
                                </td>
                                <td>{{ $passenger->passengerTypeLabel() }}</td>
                                <td>{{ $passenger->notes ?? '—' }}</td>
                                <td class="text-end">
                                    @can('managePassengers', $trip)
                                        @if($trip->isEditable())
                                        <form method="POST" action="{{ route('operations.transportation.passengers.destroy', [$trip, $passenger]) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('transportation.messages.confirm_remove_passenger') }}">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</div>

@can('managePassengers', $trip)
@if($trip->isEditable())
<x-modal id="addPatientModal" :title="__('transportation.actions.add_patient')">
    <form method="POST" action="{{ route('operations.transportation.passengers.store', $trip) }}" id="addPatientForm">
        @csrf
        <input type="hidden" name="passenger_type" value="patient">
        <x-form-input :label="__('transportation.fields.patient')" name="patient_id" type="select" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($campaignPatients as $patient)
                <option value="{{ $patient->id }}">{{ $patient->patient_name }} ({{ $patient->file_number ?? '#'.$patient->id }})</option>
            @endforeach
        </x-form-input>
        <x-form-input :label="__('transportation.fields.notes')" name="notes" type="textarea" />
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
        <button type="submit" form="addPatientForm" class="btn btn-primary">{{ __('common.save') }}</button>
    </x-slot:footer>
</x-modal>

<x-modal id="addMemberModal" :title="__('transportation.actions.add_member')">
    <form method="POST" action="{{ route('operations.transportation.passengers.store', $trip) }}" id="addMemberForm">
        @csrf
        <input type="hidden" name="passenger_type" value="member">
        <x-form-input :label="__('transportation.fields.member')" name="member_id" type="select" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($campaignMembers as $member)
                <option value="{{ $member->id }}">{{ $member->full_name }} — {{ $member->memberRole?->name }}</option>
            @endforeach
        </x-form-input>
        <x-form-input :label="__('transportation.fields.notes')" name="notes" type="textarea" />
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
        <button type="submit" form="addMemberForm" class="btn btn-primary">{{ __('common.save') }}</button>
    </x-slot:footer>
</x-modal>
@endif
@endcan
@endsection
