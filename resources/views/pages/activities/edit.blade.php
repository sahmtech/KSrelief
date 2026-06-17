@extends('layouts.admin')

@section('title', __('activities.edit_title'))

@section('content')
<x-page-header :title="__('activities.edit_title')" :subtitle="$activity->title" :breadcrumbs="[
    ['label' => __('menu.operations')], ['label' => __('activities.title'), 'url' => route('operations.activities.index')], ['label' => $activity->title],
]" />
<x-card>
    <form method="POST" action="{{ route('operations.activities.update', $activity) }}">@csrf @method('PUT')
        @include('pages.activities.partials.form', ['activity'=>$activity,'campaigns'=>$campaigns,'activityTypes'=>$activityTypes,'patientStages'=>$patientStages,'selectedCampaignId'=>$activity->campaign_id])
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('common.save') }}</button>
            <a href="{{ route('operations.activities.show', $activity) }}" class="btn btn-light">{{ __('common.cancel') }}</a>
        </div>
    </form>
</x-card>
@endsection
