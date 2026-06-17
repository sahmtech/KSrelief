@extends('layouts.admin')

@section('title', __('activities.calendar_title'))

@push('styles')
@vite(['resources/scss/activities-calendar.scss'])
@endpush

@section('content')
<x-page-header :title="__('activities.calendar_title')" :subtitle="__('activities.calendar_subtitle')"
    :breadcrumbs="[['label'=>__('menu.operations')],['label'=>__('activities.title'),'url'=>route('operations.activities.index')],['label'=>__('activities.calendar_title')]]">
    <a href="{{ route('operations.activities.index') }}" class="btn btn-outline-secondary btn-sm me-1"><i class="ti ti-list me-1"></i>{{ __('activities.actions.list') }}</a>
    @if($canCreate)<a href="{{ route('operations.activities.create') }}" class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i>{{ __('activities.actions.create') }}</a>@endif
</x-page-header>

<x-card :compact="true" class="mb-3">
    <form method="GET" action="{{ route('operations.activities.calendar') }}" class="row g-3 align-items-end" id="calendarFilters">
        <div class="col-md-4"><label class="form-label small">{{ __('activities.filters.campaign') }}</label>
            <select name="campaign_id" class="form-select form-select-sm" id="filterCampaign">
                <option value="">{{ __('activities.filters.all_campaigns') }}</option>
                @foreach($campaigns as $c)<option value="{{ $c->id }}" @selected((string)$filters['campaign_id']===(string)$c->id)>{{ $c->name }}</option>@endforeach
            </select></div>
        <div class="col-md-4"><label class="form-label small">{{ __('activities.filters.type') }}</label>
            <select name="activity_type_id" class="form-select form-select-sm" id="filterType">
                <option value="">{{ __('activities.filters.all_types') }}</option>
                @foreach($activityTypes as $t)<option value="{{ $t->id }}" @selected((string)$filters['activity_type_id']===(string)$t->id)>{{ $t->name }}</option>@endforeach
            </select></div>
        <div class="col-md-4"><button type="submit" class="btn btn-primary btn-sm">{{ __('activities.filters.apply') }}</button></div>
    </form>
</x-card>

<x-card :flush="true">
    <div id="activitiesCalendar" class="activities-calendar"
         data-events-url="{{ route('operations.activities.calendar.events') }}"
         data-reschedule-url="{{ url('operations/activities') }}"
         data-create-url="{{ route('operations.activities.create') }}"
         data-can-update="{{ $canUpdate ? '1' : '0' }}"
         data-locale="{{ app()->getLocale() }}"
         data-dir="{{ $htmlDir }}"></div>
</x-card>

<x-modal id="activityDetailModal" :title="__('activities.show_title')">
    <div id="activityDetailBody"></div>
    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
        <a href="#" id="activityDetailLink" class="btn btn-primary">{{ __('activities.actions.view') }}</a>
    </x-slot:footer>
</x-modal>
@endsection

@push('scripts')
@vite(['resources/js/activities-calendar.js'])
@endpush
