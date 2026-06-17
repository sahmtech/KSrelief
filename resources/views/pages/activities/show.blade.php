@extends('layouts.admin')

@section('title', $activity->title)

@section('content')
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
</div>
@endif

<x-page-header :title="$activity->title" :subtitle="$activity->campaign?->name.' · '.$activity->activity_date->format('Y-m-d')"
    :breadcrumbs="[['label'=>__('menu.operations')],['label'=>__('activities.title'),'url'=>route('operations.activities.index')],['label'=>$activity->title]]">
    @can('update',$activity) @if($activity->isEditable())
    <a href="{{ route('operations.activities.edit',$activity) }}" class="btn btn-warning btn-sm"><i class="ti ti-edit me-1"></i>{{ __('activities.actions.edit') }}</a>
    @endif @endcan
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-md-8 d-flex flex-wrap gap-2">
        <span class="badge-status {{ $activity->statusBadgeClass() }}">{{ $activity->statusLabel() }}</span>
        <span class="badge border" style="background:{{ $activity->activityType?->color }}20;color:{{ $activity->activityType?->color }}">{{ $activity->activityType?->name }}</span>
        <span class="badge bg-light text-dark border"><i class="ti ti-clock me-1"></i>{{ $activity->startTimeLabel() }} — {{ $activity->endTimeLabel() }}</span>
        @if($activity->location)<span class="badge bg-light text-dark border"><i class="ti ti-map-pin me-1"></i>{{ $activity->location }}</span>@endif
    </div>
    <div class="col-md-4 text-md-end">
        @can('changeStatus',$activity)
        @foreach($statusTransitions as $transition)
        @php $action = match($transition) {
            \App\Enums\ActivityStatus::InProgress => ['label'=>__('activities.actions.start'),'class'=>'btn-primary','confirm'=>__('activities.messages.confirm_start')],
            \App\Enums\ActivityStatus::Completed => ['label'=>__('activities.actions.complete'),'class'=>'btn-success','confirm'=>__('activities.messages.confirm_complete')],
            \App\Enums\ActivityStatus::Cancelled => ['label'=>__('activities.actions.cancel'),'class'=>'btn-outline-danger','confirm'=>__('activities.messages.confirm_cancel')],
            default => null }; @endphp
        @if($action)<form method="POST" action="{{ route('operations.activities.status.update',$activity) }}" class="d-inline">@csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $transition->value }}">
            <button type="submit" class="btn btn-sm {{ $action['class'] }} me-1" data-confirm="{{ $action['confirm'] }}">{{ $action['label'] }}</button>
        </form>@endif
        @endforeach
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><x-stats-card :label="__('activities.stats.participants')" :value="$participantStats['total']" icon="ti ti-users" variant="primary" /></div>
    <div class="col-md-4"><x-stats-card :label="__('activities.stats.patients')" :value="$participantStats['patients']" icon="ti ti-user-heart" variant="success" /></div>
    <div class="col-md-4"><x-stats-card :label="__('activities.stats.members')" :value="$participantStats['members']" icon="ti ti-stethoscope" variant="secondary" /></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <x-card :title="__('activities.fields.activity_info')">
            <div class="user-info-list">
                <div class="user-info-list__item"><div class="user-info-list__label">{{ __('activities.fields.campaign') }}</div><div class="user-info-list__value"><a href="{{ route('campaigns.show',$activity->campaign) }}">{{ $activity->campaign?->name }}</a></div></div>
                @if($activity->description)<div class="user-info-list__item"><div class="user-info-list__label">{{ __('activities.fields.description') }}</div><div class="user-info-list__value">{{ $activity->description }}</div></div>@endif
                @if($activity->patientStage)<div class="user-info-list__item"><div class="user-info-list__label">{{ __('activities.fields.workflow_stage') }}</div><div class="user-info-list__value">{{ $activity->patientStage->name }}</div></div>@endif
                <div class="user-info-list__item"><div class="user-info-list__label">{{ __('activities.table.created_by') }}</div><div class="user-info-list__value">{{ $activity->creator?->name }}</div></div>
            </div>
        </x-card>
        <x-card :title="__('activities.fields.timeline')" class="mt-3">
            @forelse($activity->statusLogs->sortByDesc('created_at') as $log)
            <div class="mb-3 pb-3 border-bottom">
                <div class="fw-medium">{{ $log->event_type?->label() }}
                    @if($log->old_status && $log->new_status) — {{ $log->old_status->label() }} → {{ $log->new_status->label() }} @endif
                </div>
                <div class="text-muted small">{{ $log->changedBy?->name }} · {{ $log->created_at?->format('Y-m-d H:i') }}</div>
                @if($log->notes)<div class="small">{{ $log->notes }}</div>@endif
            </div>
            @empty<div class="text-muted">{{ __('workflow.no_records') }}</div>@endforelse
        </x-card>
    </div>
    <div class="col-lg-6">
        <x-card :title="__('activities.participants_title')" :flush="true">
            <x-slot:actions>
                @can('manageParticipants',$activity) @if($activity->isEditable())
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkAddModal"><i class="ti ti-users-plus me-1"></i>{{ __('activities.actions.bulk_add') }}</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPatientModal"><i class="ti ti-user-heart me-1"></i>{{ __('activities.actions.add_patient') }}</button>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal"><i class="ti ti-stethoscope me-1"></i>{{ __('activities.actions.add_member') }}</button>
                @endif @endcan
            </x-slot:actions>
            @if($activity->participants->isEmpty())<div class="text-center text-muted py-4">{{ __('activities.messages.no_participants') }}</div>
            @else
            <div class="table-responsive admin-table-scroll"><table class="table table-hover mb-0"><thead><tr>
                <th>{{ __('activities.table.participant') }}</th><th>{{ __('activities.table.attendance') }}</th><th class="text-end">{{ __('activities.table.actions') }}</th>
            </tr></thead><tbody>
            @foreach($activity->participants as $p)
            <tr>
                <td>@if($p->participant_type===\App\Enums\PassengerType::Member)<a href="{{ route('medical-staff.members.show',$p->member) }}">{{ $p->member?->full_name }}</a>@else<a href="{{ route('patients.show',$p->patient) }}">{{ $p->patient?->patient_name }}</a>@endif
                    <div class="text-muted small">{{ $p->participantTypeLabel() }}</div></td>
                <td><span class="badge-status {{ $p->attendance_status?->badgeClass() }}">{{ $p->attendanceStatusLabel() }}</span></td>
                <td class="text-end">@can('manageParticipants',$activity) @if($activity->isEditable())
                    <form method="POST" action="{{ route('operations.activities.participants.destroy',[$activity,$p]) }}" class="d-inline">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" data-confirm="{{ __('activities.messages.confirm_remove') }}"><i class="ti ti-x"></i></button></form>@endif @endcan</td>
            </tr>@endforeach
            </tbody></table></div>@endif
        </x-card>
    </div>
</div>

@can('manageParticipants',$activity) @if($activity->isEditable())
<x-modal id="bulkAddModal" :title="__('activities.actions.bulk_add')" size="lg" data-participant-multiselect-modal>
    <form method="POST" action="{{ route('operations.activities.participants.bulk',$activity) }}" id="bulkAddForm" data-participant-form data-empty-message="{{ __('activities.errors.no_participants_selected') }}">@csrf
        <div class="row g-3">
            <div class="col-md-6">
                <div class="participant-multiselect" data-participant-multiselect
                    data-placeholder="{{ __('activities.hints.select_patients') }}"
                    data-i18n-no-results="{{ __('common.datatable.zero_records') }}">
                    <label class="form-label">{{ __('activities.fields.patients') }}</label>
                    <select name="patient_ids[]" class="form-select" multiple data-participant-select>
                        <option></option>
                        @foreach($campaignPatients as $p)<option value="{{ $p->id }}">{{ $p->patient_name }}</option>@endforeach
                    </select>
                    <div class="form-text">{{ __('activities.hints.multi_select') }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="participant-multiselect" data-participant-multiselect
                    data-placeholder="{{ __('activities.hints.select_members') }}"
                    data-i18n-no-results="{{ __('common.datatable.zero_records') }}">
                    <label class="form-label">{{ __('activities.fields.members') }}</label>
                    <select name="member_ids[]" class="form-select" multiple data-participant-select>
                        <option></option>
                        @foreach($campaignMembers as $m)<option value="{{ $m->id }}">{{ $m->full_name }}</option>@endforeach
                    </select>
                    <div class="form-text">{{ __('activities.hints.multi_select') }}</div>
                </div>
            </div>
        </div>
    </form>
    <x-slot:footer><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
        <button type="submit" form="bulkAddForm" class="btn btn-primary">{{ __('activities.actions.bulk_add') }}</button></x-slot:footer>
</x-modal>
<x-modal id="addPatientModal" :title="__('activities.actions.add_patient')" data-participant-multiselect-modal>
    <form method="POST" action="{{ route('operations.activities.participants.bulk',$activity) }}" id="addPatientForm" data-participant-form data-empty-message="{{ __('activities.errors.no_participants_selected') }}">@csrf
        <div class="participant-multiselect" data-participant-multiselect
            data-placeholder="{{ __('activities.hints.select_patients') }}"
            data-i18n-no-results="{{ __('common.datatable.zero_records') }}">
            <label class="form-label">{{ __('activities.fields.patients') }}</label>
            <select name="patient_ids[]" class="form-select" multiple data-participant-select>
                <option></option>
                @foreach($campaignPatients as $p)<option value="{{ $p->id }}">{{ $p->patient_name }}</option>@endforeach
            </select>
            <div class="form-text">{{ __('activities.hints.multi_select') }}</div>
        </div>
    </form>
    <x-slot:footer><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
        <button type="submit" form="addPatientForm" class="btn btn-primary">{{ __('activities.actions.add_patient') }}</button></x-slot:footer>
</x-modal>
<x-modal id="addMemberModal" :title="__('activities.actions.add_member')" data-participant-multiselect-modal>
    <form method="POST" action="{{ route('operations.activities.participants.bulk',$activity) }}" id="addMemberForm" data-participant-form data-empty-message="{{ __('activities.errors.no_participants_selected') }}">@csrf
        <div class="participant-multiselect" data-participant-multiselect
            data-placeholder="{{ __('activities.hints.select_members') }}"
            data-i18n-no-results="{{ __('common.datatable.zero_records') }}">
            <label class="form-label">{{ __('activities.fields.members') }}</label>
            <select name="member_ids[]" class="form-select" multiple data-participant-select>
                <option></option>
                @foreach($campaignMembers as $m)<option value="{{ $m->id }}">{{ $m->full_name }}</option>@endforeach
            </select>
            <div class="form-text">{{ __('activities.hints.multi_select') }}</div>
        </div>
    </form>
    <x-slot:footer><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
        <button type="submit" form="addMemberForm" class="btn btn-primary">{{ __('activities.actions.add_member') }}</button></x-slot:footer>
</x-modal>
@endif @endcan
@endsection
