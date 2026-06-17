@extends('layouts.admin')

@section('title', __('patients.create_title'))

@section('content')
<x-page-header
    :title="__('patients.create_title')"
    :subtitle="__('patients.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title'), 'url' => route('patients.index')],
        ['label' => __('patients.create_title')],
    ]"
/>

<form method="POST" action="{{ route('patients.store') }}" enctype="multipart/form-data">
    @csrf
    @include('pages.patients.partials.form', [
        'campaigns' => $campaigns,
        'selectedCampaignId' => $selectedCampaignId,
        'eligibilityStatuses' => $eligibilityStatuses,
        'patientStages' => $patientStages,
        'genders' => $genders,
        'admissionStatuses' => $admissionStatuses,
        'recordStatuses' => $recordStatuses,
    ])
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
