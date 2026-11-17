@extends('layouts.admin')

@section('title', __('beneficiary_registration.view'))

@section('content')
@php
    $payload = $application->application_payload ?? [];
    $health = $payload['health'] ?? [];
    $extra = $payload['extra'] ?? [];
    $attachmentsByField = $application->attachments->groupBy('field_key');
@endphp

<x-page-header
    :title="$application->full_name"
    :subtitle="__('beneficiary_registration.view')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('beneficiary_registration.menu'), 'url' => route('beneficiary-applications.index')],
        ['label' => $application->full_name],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('beneficiary-applications.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i> {{ __('patients.show.back') }}
        </a>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">{{ __('beneficiary_registration.steps.basic') }}</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.extra_type') }}</dt>
                    <dd class="col-sm-8">{{ $application->extraTypeLabel() }}</dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.name') }}</dt>
                    <dd class="col-sm-8">{{ $application->full_name }}</dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.national_number') }}</dt>
                    <dd class="col-sm-8">{{ $application->national_number }}</dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.gender') }}</dt>
                    <dd class="col-sm-8">{{ $application->genderLabel() }}</dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.mobile') }}</dt>
                    <dd class="col-sm-8">{{ $application->mobile }}</dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.country') }}</dt>
                    <dd class="col-sm-8">{{ config('beneficiary_registration.countries.'.$application->country_code, $application->country_code) }}</dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.birth_date') }}</dt>
                    <dd class="col-sm-8">{{ $application->birth_date?->format('Y-m-d') }}</dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.marital_status') }}</dt>
                    <dd class="col-sm-8">
                        @php
                            $maritalId = (int) ($payload['marital_status'] ?? 0);
                            $marital = config("beneficiary_registration.marital_status.{$maritalId}", []);
                            $genderKey = config("beneficiary_registration.gender.{$application->gender}", 'male');
                        @endphp
                        {{ $marital[$genderKey] ?? '—' }}
                    </dd>
                    <dt class="col-sm-4">{{ __('beneficiary_registration.reference') }}</dt>
                    <dd class="col-sm-8"><code>{{ $application->uuid }}</code></dd>
                </dl>
            </div>
        </div>

        @if(filled($extra['case_description'] ?? null) || filled($extra['email'] ?? null) || ($attachmentsByField['medical_reports'] ?? collect())->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">{{ __('beneficiary_registration.steps.extra') }}</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">{{ __('beneficiary_registration.fields.case_description') }}</dt>
                        <dd class="col-sm-8" style="white-space: pre-wrap;">{{ $extra['case_description'] ?? '—' }}</dd>
                        <dt class="col-sm-4">{{ __('beneficiary_registration.fields.email') }}</dt>
                        <dd class="col-sm-8">{{ $extra['email'] ?? $application->email ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">{{ __('beneficiary_registration.steps.health') }}</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">{{ __('beneficiary_registration.fields.health_status') }}</dt>
                    <dd class="col-sm-8">
                        {{ (int) ($health['health_status'] ?? 1) === 2
                            ? __('beneficiary_registration.options.health_sick')
                            : __('beneficiary_registration.options.health_healthy') }}
                    </dd>
                    @if((int) ($health['health_status'] ?? 1) === 2)
                        <dt class="col-sm-4">{{ __('beneficiary_registration.fields.handicapped') }}</dt>
                        <dd class="col-sm-8">{{ !empty($health['handicapped']) ? __('beneficiary_registration.options.yes') : __('beneficiary_registration.options.no') }}</dd>
                        <dt class="col-sm-4">{{ __('beneficiary_registration.fields.handicapped_percentage') }}</dt>
                        <dd class="col-sm-8">{{ $health['handicapped_percentage'] ?? '—' }}{{ isset($health['handicapped_percentage']) ? '%' : '' }}</dd>
                        <dt class="col-sm-4">{{ __('beneficiary_registration.fields.diseases') }}</dt>
                        <dd class="col-sm-8">
                            @forelse($health['diseases'] ?? [] as $disease)
                                @php
                                    $diseaseLabel = config('beneficiary_registration.diseases.'.($disease['disease'] ?? ''), '—');
                                    $typeLabel = config('beneficiary_registration.disease_types.'.($disease['type'] ?? ''), '—');
                                    $treatmentLabel = config('beneficiary_registration.treatment_types.'.($disease['treatment_type'] ?? ''), '—');
                                @endphp
                                <div class="border rounded p-2 mb-2">
                                    <div><strong>{{ $diseaseLabel }}</strong> — {{ $typeLabel }} / {{ $treatmentLabel }}</div>
                                    @if(filled($disease['treatment_cost'] ?? null))
                                        <div class="text-muted">{{ __('beneficiary_registration.fields.treatment_cost') }}: {{ $disease['treatment_cost'] }}</div>
                                    @endif
                                    @if(filled($disease['notes'] ?? null))
                                        <div class="text-muted">{{ $disease['notes'] }}</div>
                                    @endif
                                </div>
                            @empty
                                —
                            @endforelse
                        </dd>
                        <dt class="col-sm-4">{{ __('beneficiary_registration.fields.health_notes') }}</dt>
                        <dd class="col-sm-8" style="white-space: pre-wrap;">{{ $health['health_notes'] ?? '—' }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">{{ __('beneficiary_registration.attachments') }}</h6>
            </div>
            <div class="card-body">
                @forelse($application->attachments as $attachment)
                    @php
                        $fieldLabelKey = 'beneficiary_registration.fields.'.$attachment->field_key;
                        $fieldLabel = __($fieldLabelKey);
                        if ($fieldLabel === $fieldLabelKey) {
                            $fieldLabel = $attachment->field_key === 'photo'
                                ? __('beneficiary_registration.photo')
                                : $attachment->field_key;
                        }
                    @endphp
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <div class="fw-semibold small">{{ $fieldLabel }}</div>
                            <div class="text-muted small">{{ $attachment->original_name }}</div>
                        </div>
                        <a class="btn btn-sm btn-outline-secondary"
                           href="{{ route('beneficiary-applications.attachments.download', [$application, $attachment]) }}">
                            {{ __('beneficiary_registration.download') }}
                        </a>
                    </div>
                @empty
                    <p class="text-muted mb-0">—</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge text-bg-{{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
                </div>
                <dl class="row small mb-0">
                    <dt class="col-5">{{ __('common.created_at') }}</dt>
                    <dd class="col-7">{{ $application->created_at?->format('Y-m-d H:i') }}</dd>
                    @if($application->reviewed_at)
                        <dt class="col-5">{{ __('beneficiary_registration.reviewed_at') }}</dt>
                        <dd class="col-7">{{ $application->reviewed_at->format('Y-m-d H:i') }}</dd>
                        <dt class="col-5">{{ __('beneficiary_registration.reviewed_by') }}</dt>
                        <dd class="col-7">{{ $application->reviewer?->name ?? '—' }}</dd>
                    @endif
                    @if($application->rejection_reason)
                        <dt class="col-5">{{ __('beneficiary_registration.rejection_reason') }}</dt>
                        <dd class="col-7">{{ $application->rejection_reason }}</dd>
                    @endif
                    @if($application->acceptedPatient)
                        <dt class="col-5">{{ __('beneficiary_registration.accepted_patient') }}</dt>
                        <dd class="col-7">
                            <a href="{{ route('patients.show', $application->acceptedPatient) }}">
                                {{ $application->acceptedPatient->patient_name }}
                            </a>
                        </dd>
                        <dt class="col-5">{{ __('beneficiary_registration.choose_campaign') }}</dt>
                        <dd class="col-7">{{ $application->acceptedCampaign?->name ?? '—' }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @can('review', $application)
            @if($application->isPending())
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-semibold">{{ __('beneficiary_registration.accept_title') }}</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('beneficiary-applications.accept', $application) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('beneficiary_registration.choose_campaign') }} <span class="text-danger">*</span></label>
                                <select name="campaign_id" class="form-select" required>
                                    <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign['id'] }}">{{ $campaign['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">{{ __('beneficiary_registration.accept') }}</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-semibold">{{ __('beneficiary_registration.reject_title') }}</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('beneficiary-applications.reject', $application) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('beneficiary_registration.rejection_reason') }} <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="{{ __('beneficiary_registration.rejection_reason_placeholder') }}" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">{{ __('beneficiary_registration.reject') }}</button>
                        </form>
                    </div>
                </div>
            @endif
        @endcan
    </div>
</div>
@endsection
