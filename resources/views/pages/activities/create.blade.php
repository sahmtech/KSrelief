@extends('layouts.admin')

@section('title', __('activities.create_title'))

@section('content')
<x-page-header :title="__('activities.create_title')" :breadcrumbs="[
    ['label' => __('menu.operations')], ['label' => __('activities.title'), 'url' => route('operations.activities.index')], ['label' => __('activities.create_title')],
]" />
<x-card>
    <form method="POST" action="{{ route('operations.activities.store') }}">@csrf
        @include('pages.activities.partials.form', compact('campaigns', 'activityTypes', 'patientStages', 'selectedCampaignId', 'prefillDate', 'prefillStart', 'prefillEnd'))
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('common.save') }}</button>
            <a href="{{ route('operations.activities.index') }}" class="btn btn-light">{{ __('common.cancel') }}</a>
        </div>
    </form>
</x-card>
@endsection
