@extends('layouts.admin')

@section('title', $patient->patient_name)

@section('content')
@include('pages.patients.partials.clinical-fallback-styles')

@push('styles')
<style>
    .patient-brief-hero{display:flex;align-items:flex-start;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;padding:1.25rem 1.5rem;background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:1rem}
    .patient-brief-hero__main{display:flex;align-items:center;gap:1rem;min-width:0}
    .patient-brief-hero__name{font-size:1.5rem;font-weight:700;margin-bottom:.35rem}
    .patient-brief-hero__meta{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;font-size:.875rem;color:#64748b;margin-bottom:.5rem}
    .patient-brief-hero__code{font-size:.8125rem;font-weight:600;color:#0f766e;background:rgba(15,118,110,.08);padding:.15rem .45rem;border-radius:.35rem}
    .patient-brief-hero__badges{display:flex;flex-wrap:wrap;gap:.35rem}
    .patient-brief-chips{display:flex;flex-wrap:wrap;gap:.75rem}
    .patient-brief-chip{min-width:110px;padding:.625rem .875rem;border-radius:.75rem;background:#f8fafc;border:1px solid #e2e8f0}
    .patient-brief-chip--highlight{background:rgba(15,118,110,.06);border-color:rgba(15,118,110,.2)}
    .patient-brief-chip__label{font-size:.7rem;text-transform:uppercase;letter-spacing:.03em;color:#64748b;margin-bottom:.2rem}
    .patient-brief-chip__value{font-size:.9375rem;font-weight:600;color:#1e293b}
    .patient-brief-field{padding:.75rem;border-radius:.5rem;background:#f8fafc;border:1px solid rgba(0,0,0,.04);height:100%}
    .patient-brief-field__label{font-size:.75rem;font-weight:600;color:#64748b;margin-bottom:.35rem}
    .patient-brief-field__value{font-size:.875rem;font-weight:500;color:#1e293b}
    .patient-brief-stage-card{height:100%;padding:.875rem;border-radius:.75rem;background:#f8fafc;border:1px solid #e2e8f0}
    .patient-brief-stage-card__title{font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#0f766e;margin-bottom:.625rem;padding-bottom:.5rem;border-bottom:2px solid rgba(15,118,110,.15)}
    .patient-brief-stage-card__list{list-style:none;margin:0;padding:0}
    .patient-brief-stage-card__list li{margin-bottom:.5rem;font-size:.8125rem}
    .patient-brief-stage-card__list .label{display:block;color:#64748b;font-size:.7rem}
    .patient-brief-stage-card__list .value{display:block;color:#1e293b;font-weight:500}
</style>
@endpush

<x-page-header
    :title="__('patients.brief.title')"
    :subtitle="__('patients.brief.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-id me-1"></i>{{ __('patients.brief.full_profile') }}
        </a>
        @can('update', $patient)
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-pencil me-1"></i>{{ __('patients.actions.edit') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

{{-- Identity strip --}}
<div class="patient-brief-hero mb-4">
    <div class="patient-brief-hero__main">
        <x-patient-avatar :patient="$patient" size="lg" />
        <div class="patient-brief-hero__identity">
            <h1 class="patient-brief-hero__name">{{ $patient->patient_name }}</h1>
            <div class="patient-brief-hero__meta">
                @if($patient->file_number)
                    <code class="patient-brief-hero__code">{{ $patient->file_number }}</code>
                @endif
                <span class="text-muted">·</span>
                <span>{{ $patient->ageLabel() }}</span>
                @if($patient->gender)
                    <span class="text-muted">·</span>
                    <span>{{ $patient->gender->label() }}</span>
                @endif
                @if(filled($patient->height_cm) || filled($patient->weight_kg))
                    <span class="text-muted">·</span>
                    <span>
                        @if(filled($patient->height_cm)){{ $patient->heightLabel() }}@endif
                        @if(filled($patient->height_cm) && filled($patient->weight_kg)) / @endif
                        @if(filled($patient->weight_kg)){{ $patient->weightLabel() }}@endif
                    </span>
                @endif
            </div>
            <div class="patient-brief-hero__badges">
                @if($patient->eligibilityStatus)
                    <span class="badge border" style="background-color: {{ $patient->eligibilityStatus->color }}20; color: {{ $patient->eligibilityStatus->color }};">
                        {{ $patient->eligibilityStatus->name }}
                    </span>
                @endif
                @if($patient->currentStage)
                    <span class="badge bg-light text-dark border">
                        <i class="ti ti-stairs me-1"></i>{{ $patient->currentStage->name }}
                    </span>
                @endif
                <span class="badge-status {{ $patient->admissionBadgeClass() }}">{{ $patient->admissionLabel() }}</span>
            </div>
        </div>
    </div>
    @if($patient->campaign)
        <div class="patient-brief-hero__campaign">
            <div class="text-muted small">{{ __('patients.fields.campaign') }}</div>
            <a href="{{ route('campaigns.show', $patient->campaign) }}" class="fw-semibold text-decoration-none">
                {{ $patient->campaign->name }}
            </a>
            @if($patient->campaign->code)
                <div class="text-muted small"><code>{{ $patient->campaign->code }}</code></div>
            @endif
        </div>
    @endif
</div>

@if(filled($patient->notes) || filled($patient->approval_reason))
    <div class="alert alert-warning border-0 mb-4" style="font-size: 0.875rem;">
        <i class="ti ti-alert-triangle me-2"></i>
        @if(filled($patient->approval_reason))
            <strong>{{ __('patients.fields.approval_reason') }}:</strong> {{ $patient->approval_reason }}
        @endif
        @if(filled($patient->notes))
            @if(filled($patient->approval_reason))<br>@endif
            <strong>{{ __('patients.fields.notes') }}:</strong> {{ $patient->notes }}
        @endif
    </div>
@endif

{{-- Surgery context chips --}}
@if(!empty($brief['surgery_context']))
    <x-card :title="__('patients.brief.surgery_context')" :compact="true" class="mb-4">
        <div class="patient-brief-chips">
            @foreach($brief['surgery_context'] as $item)
                <div @class(['patient-brief-chip', 'patient-brief-chip--highlight' => !empty($item['highlight'])])>
                    <div class="patient-brief-chip__label">{{ $item['label'] }}</div>
                    <div class="patient-brief-chip__value">
                        <x-clinical-value
                            :value="$item['value']"
                            :type="$item['type'] ?? null"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
@endif

<div class="row g-3 mb-4">
    {{-- Priority clinical — doctor focus --}}
    <div class="col-lg-8">
        <x-card :title="__('patients.brief.priority_clinical')" class="h-100">
            @if(!empty($brief['priority_clinical']))
                <div class="row g-2">
                    @foreach($brief['priority_clinical'] as $item)
                        <div class="col-md-6">
                            <div class="patient-brief-field">
                                <div class="patient-brief-field__label">{{ $item['label'] }}</div>
                                <div class="patient-brief-field__value">
                                    @if(!empty($item['color']))
                                        <span class="fw-semibold" style="color: {{ $item['color'] }};">{{ $item['value'] }}</span>
                                    @else
                                        <x-clinical-value
                                            :value="$item['value']"
                                            :type="$item['type'] ?? null"
                                            :link-label="$item['label']"
                                        />
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0 small">{{ __('patients.clinical.no_phase_data') }}</p>
            @endif
        </x-card>
    </div>

    {{-- Demographics sidebar --}}
    <div class="col-lg-4">
        <x-card :title="__('patients.brief.demographics')" class="h-100">
            <div class="user-info-list">
                @foreach($brief['demographics'] as $item)
                    <div class="user-info-list__item">
                        <div class="user-info-list__label">{{ $item['label'] }}</div>
                        <div class="user-info-list__value">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>

@include('pages.patients.partials.brief-attachments', ['patient' => $patient])

{{-- Stage summaries --}}
@if(!empty($brief['stage_summaries']))
    <x-card :title="__('patients.brief.stage_records')" class="mb-4">
        <div class="row g-3">
            @foreach($brief['stage_summaries'] as $stage)
                <div class="col-md-6 col-xl-3">
                    <div class="patient-brief-stage-card">
                        <div class="patient-brief-stage-card__title">{{ $stage['name'] }}</div>
                        <ul class="patient-brief-stage-card__list">
                            @foreach(array_slice($stage['items'], 0, 5) as $item)
                                <li>
                                    <span class="label">{{ $item['label'] }}</span>
                                    <span class="value">
                                        <x-clinical-value :value="$item['value']" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
@endif

{{-- Full clinical phases --}}
@if($clinicalProfile && !empty($brief['phases']))
    <x-card :title="__('patients.brief.clinical_phases')" :flush="true">
        @foreach($brief['phases'] as $phaseCode => $phase)
            <div class="clinical-phase-panel m-3" style="--clinical-phase-bg: {{ $phase['background'] }}; --clinical-phase-color: {{ $phase['color'] }};">
                <div class="clinical-phase-panel__header">
                    <h6 class="mb-0">{{ $phase['label'] }}</h6>
                    <span class="badge bg-light text-dark border">{{ count($phase['items']) }}</span>
                </div>
                <div class="clinical-phase-panel__body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('patients.clinical.field') }}</th>
                                    <th>{{ __('patients.clinical.value') }}</th>
                                    <th>{{ __('patients.clinical.source') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($phase['items'] as $item)
                                    <tr>
                                        <td class="fw-medium">{{ $item['label'] }}</td>
                                        <td>
                                            <x-clinical-value
                                                :value="$item['value']"
                                                :type="$item['type'] ?? null"
                                                :link-label="$item['label']"
                                            />
                                        </td>
                                        <td class="text-muted small">{{ $item['source'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </x-card>
@endif

<div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-primary btn-sm">
        <i class="ti ti-id me-1"></i>{{ __('patients.brief.full_profile') }}
    </a>
    @can('viewAny', [\App\Models\MedicalRecord::class, $patient])
        <a href="{{ route('patients.show', $patient) }}#records-dossier" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-file-medical me-1"></i>{{ __('patients.tabs.records') }}
        </a>
    @endcan
    @can('create', [\App\Models\MedicalRecord::class, $patient])
        <a href="{{ route('patients.records.create', $patient) }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i>{{ __('workflow.records.add') }}
        </a>
    @endcan
</div>
@endsection
