@extends('layouts.admin')

@section('title', $patient->patient_name)

@section('content')
<x-page-header
    :title="__('patients.show_title')"
    :subtitle="__('patients.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name],
    ]"
/>

<div class="user-profile-hero">
    <div class="user-profile-hero__banner"></div>
    <div class="user-profile-hero__body">
        <div class="user-profile-hero__header">
            <div class="user-profile-hero__identity">
                <div class="user-profile-hero__avatar">
                    <x-patient-avatar :patient="$patient" size="xl" />
                </div>
                <div class="user-profile-hero__info">
                    <h1 class="user-profile-hero__name">{{ $patient->patient_name }}</h1>
                    <p class="user-profile-hero__email">
                        <a href="{{ route('campaigns.show', $patient->campaign) }}" class="text-decoration-none">
                            {{ $patient->campaign->name }}
                        </a>
                        @if($patient->file_number)
                            · <x-record-code-link :href="route('patients.show', $patient)" :code="$patient->file_number" />
                        @endif
                    </p>
                    <div class="user-profile-hero__meta">
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
                        <span class="badge-status {{ $patient->recordStatusBadgeClass() }}">{{ $patient->recordStatusLabel() }}</span>
                    </div>
                </div>
            </div>
            <div class="user-profile-hero__actions">
                @can('update', $patient)
                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-pencil me-1"></i> {{ __('patients.actions.edit') }}
                    </a>
                @endcan
                @can('delete', $patient)
                    <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('patients.messages.confirm_delete') }}">
                            <i class="ti ti-trash me-1"></i> {{ __('patients.actions.delete') }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="patientTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab">
            <i class="ti ti-info-circle me-1"></i> {{ __('patients.tabs.overview') }}
        </button>
    </li>
    @can('viewWorkflow', $patient)
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="workflow-tab" data-bs-toggle="tab" data-bs-target="#workflow-pane" type="button" role="tab">
            <i class="ti ti-route me-1"></i> {{ __('patients.tabs.workflow') }}
        </button>
    </li>
    @endcan
    @can('viewAny', [\App\Models\MedicalRecord::class, $patient])
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="records-tab" data-bs-toggle="tab" data-bs-target="#records-pane" type="button" role="tab">
            <i class="ti ti-file-medical me-1"></i> {{ __('patients.tabs.records') }}
        </button>
    </li>
    @endcan
    @can('viewStageHistory', $patient)
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">
            <i class="ti ti-history me-1"></i> {{ __('patients.tabs.history') }}
        </button>
    </li>
    @endcan
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attachments-tab" data-bs-toggle="tab" data-bs-target="#attachments-pane" type="button" role="tab">
            <i class="ti ti-paperclip me-1"></i> {{ __('patients.tabs.attachments') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $patient->attachments->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-pane" type="button" role="tab">
            <i class="ti ti-report-analytics me-1"></i> {{ __('patients.tabs.reports') }}
        </button>
    </li>
    @if(config('admin.show_patient_transportation_tab'))
    @can('transportation.view')
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transportation-tab" data-bs-toggle="tab" data-bs-target="#transportation-pane" type="button" role="tab">
            <i class="ti ti-bus me-1"></i> {{ __('patients.tabs.transportation') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $transportStats['total'] }}</span>
        </button>
    </li>
    @endcan
    @endif
    @can('activity.view')
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane" type="button" role="tab">
            <i class="ti ti-activity me-1"></i> {{ __('patients.tabs.activities') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $activityStats['total'] }}</span>
        </button>
    </li>
    @endcan
</ul>

<div class="tab-content" id="patientTabsContent">
    <div class="tab-pane fade show active" id="overview-pane" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-6">
                <x-card :title="__('patients.sections.basic')">
                    <div class="user-info-list">
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.patient_name') }}</div>
                            <div class="user-info-list__value">{{ $patient->patient_name }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.file_number') }}</div>
                            <div class="user-info-list__value">
                                @if($patient->file_number)
                                    <x-record-code-link :href="route('patients.show', $patient)" :code="$patient->file_number" />
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.date_of_birth') }}</div>
                            <div class="user-info-list__value">{{ $patient->date_of_birth->format('Y-m-d') }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.age') }}</div>
                            <div class="user-info-list__value">{{ $patient->ageLabel() }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.gender') }}</div>
                            <div class="user-info-list__value">{{ $patient->gender?->label() ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.height_cm') }}</div>
                            <div class="user-info-list__value">{{ $patient->heightLabel() }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.weight_kg') }}</div>
                            <div class="user-info-list__value">{{ $patient->weightLabel() }}</div>
                        </div>
                    </div>
                </x-card>

                <x-card :title="__('patients.sections.contact')" class="mt-3">
                    <div class="user-info-list">
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.contact_number') }}</div>
                            <div class="user-info-list__value">{{ $patient->contact_number ?? '—' }}</div>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="col-lg-6">
                <x-card :title="__('patients.sections.campaign')">
                    <div class="user-info-list">
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.campaign') }}</div>
                            <div class="user-info-list__value">
                                <a href="{{ route('campaigns.show', $patient->campaign) }}" class="text-decoration-none fw-medium">
                                    {{ $patient->campaign->name }}
                                </a>
                            </div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.country') }}</div>
                            <div class="user-info-list__value">{{ $patient->campaign->country?->localizedName() ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.city') }}</div>
                            <div class="user-info-list__value">{{ $patient->campaign->city?->localizedName() ?? '—' }}</div>
                        </div>
                    </div>
                </x-card>

                <x-card :title="__('patients.sections.medical')" class="mt-3">
                    <div class="user-info-list">
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.eligibility_status') }}</div>
                            <div class="user-info-list__value">
                                @if($patient->eligibilityStatus)
                                    <span class="badge border" style="background-color: {{ $patient->eligibilityStatus->color }}20; color: {{ $patient->eligibilityStatus->color }};">
                                        {{ $patient->eligibilityStatus->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.current_stage') }}</div>
                            <div class="user-info-list__value">{{ $patient->currentStage?->name ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.surgery_day_number') }}</div>
                            <div class="user-info-list__value">{{ $patient->surgeryDayLabel() }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.rank') }}</div>
                            <div class="user-info-list__value">{{ $patient->rank ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.surgical_side') }}</div>
                            <div class="user-info-list__value">{{ $patient->surgicalSideLabel() }}</div>
                        </div>
                        @if($patient->approval_reason)
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.approval_reason') }}</div>
                            <div class="user-info-list__value">{{ $patient->approval_reason }}</div>
                        </div>
                        @endif
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.admission_status') }}</div>
                            <div class="user-info-list__value">
                                <span class="badge-status {{ $patient->admissionBadgeClass() }}">{{ $patient->admissionLabel() }}</span>
                            </div>
                        </div>
                        @if($patient->notes)
                            <div class="user-info-list__item">
                                <div class="user-info-list__label">{{ __('patients.fields.notes') }}</div>
                                <div class="user-info-list__value">{{ $patient->notes }}</div>
                            </div>
                        @endif
                    </div>
                </x-card>

                <x-card :title="__('patients.sections.audit')" class="mt-3">
                    <div class="user-info-list">
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.created_by') }}</div>
                            <div class="user-info-list__value">{{ $patient->creator?->name ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.created_at') }}</div>
                            <div class="user-info-list__value">{{ $patient->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.updated_by') }}</div>
                            <div class="user-info-list__value">{{ $patient->updater?->name ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('patients.fields.updated_at') }}</div>
                            <div class="user-info-list__value">{{ $patient->updated_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    {{-- Medical Workflow Tab --}}
    @can('viewWorkflow', $patient)
    <div class="tab-pane fade" id="workflow-pane" role="tabpanel">
        @include('pages.patients.partials.workflow-tab')
    </div>
    @endcan

    {{-- Medical Records Tab (includes Excel-like clinical dossier) --}}
    @can('viewAny', [\App\Models\MedicalRecord::class, $patient])
    <div class="tab-pane fade" id="records-pane" role="tabpanel">
        @include('pages.patients.partials.records-tab', [
            'patient' => $patient,
            'medicalRecords' => $medicalRecords,
            'clinicalProfile' => $clinicalProfile,
        ])
    </div>
    @endcan

    {{-- Stage History Tab --}}
    @can('viewStageHistory', $patient)
    <div class="tab-pane fade" id="history-pane" role="tabpanel">
        @include('pages.patients.partials.history-tab')
    </div>
    @endcan

    <div class="tab-pane fade" id="attachments-pane" role="tabpanel">
        @include('pages.patients.partials.attachments', ['patient' => $patient])
    </div>

    <div class="tab-pane fade" id="reports-pane" role="tabpanel">
        <x-card>
            <div class="text-center text-muted py-5">
                <i class="ti ti-report-analytics d-block mb-2" style="font-size: 2.5rem; opacity: 0.4;"></i>
                {{ __('patients.future.reports') }}
            </div>
        </x-card>
    </div>

    @if(config('admin.show_patient_transportation_tab'))
    @can('transportation.view')
    <div class="tab-pane fade" id="transportation-pane" role="tabpanel">
        @include('pages.patients.partials.transportation-tab', [
            'patient' => $patient,
            'transportStats' => $transportStats,
            'patientTrips' => $patientTrips,
        ])
    </div>
    @endcan
    @endif

    @can('activity.view')
    <div class="tab-pane fade" id="activities-pane" role="tabpanel">
        @include('pages.patients.partials.activities-tab', [
            'patient' => $patient,
            'activityStats' => $activityStats,
            'patientActivities' => $patientActivities,
        ])
    </div>
    @endcan
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    const tabMap = {
        '#attachments': 'attachments-tab',
        '#workflow': 'workflow-tab',
        '#clinical': 'records-tab',
        '#records': 'records-tab',
        '#records-dossier': 'records-tab',
        '#records-list': 'records-tab',
        '#history': 'history-tab',
        '#transportation': 'transportation-tab',
        '#activities': 'activities-tab',
    };
    const tabId = tabMap[hash];

    if (tabId) {
        const tab = document.getElementById(tabId);
        if (tab) {
            bootstrap.Tab.getOrCreateInstance(tab).show();
        }
    }

    const recordsSubMap = {
        '#clinical': 'records-dossier-tab',
        '#records': 'records-dossier-tab',
        '#records-dossier': 'records-dossier-tab',
        '#records-list': 'records-list-tab',
    };
    const recordsSubId = recordsSubMap[hash];

    if (recordsSubId) {
        const subTab = document.getElementById(recordsSubId);
        if (subTab) {
            bootstrap.Tab.getOrCreateInstance(subTab).show();
        }
    }

    @can('viewWorkflow', $patient)
    @if($errors->has('to_stage_id'))
    const workflowTab = document.getElementById('workflow-tab');
    if (workflowTab) {
        bootstrap.Tab.getOrCreateInstance(workflowTab).show();
        const modal = document.getElementById('changeStageModal');
        if (modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    }
    @endif
    @endcan
});
</script>
@endpush
