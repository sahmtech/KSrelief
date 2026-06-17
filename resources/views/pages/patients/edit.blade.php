@extends('layouts.admin')

@section('title', __('patients.edit_title'))

@section('content')
<x-page-header
    :title="__('patients.edit_title')"
    :subtitle="__('patients.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
        ['label' => __('patients.edit_title')],
    ]"
/>

<form method="POST" action="{{ route('patients.update', $patient) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('pages.patients.partials.form', [
        'patient' => $patient,
        'campaigns' => $campaigns,
        'selectedCampaignId' => $selectedCampaignId,
        'eligibilityStatuses' => $eligibilityStatuses,
        'patientStages' => $patientStages,
        'genders' => $genders,
        'admissionStatuses' => $admissionStatuses,
        'recordStatuses' => $recordStatuses,
    ])
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save') }}</button>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
