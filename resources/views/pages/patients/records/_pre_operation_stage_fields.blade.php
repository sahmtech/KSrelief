@include('pages.patients.partials.clinical-fallback-styles')

@php
    $phaseStyle = config('patient_clinical.phases.pre_op', []);
    $recordModel = $record ?? null;
    $patientModel = $patient ?? null;
    $isPatientCreate = (bool) ($patientCreateMode ?? false) || ! $patientModel;

    $clinicalSelectOptionUrl = $isPatientCreate
        ? ''
        : route('patients.records.clinical-select-options.store', $patient);
    $ctOptionUrl = $isPatientCreate
        ? ''
        : ($ctOptionUrl ?? route('patients.records.imaging-options.ct.store', $patient));
    $mriOptionUrl = $isPatientCreate
        ? ''
        : ($mriOptionUrl ?? route('patients.records.imaging-options.mri.store', $patient));
    $expectationOptionUrl = $isPatientCreate
        ? ''
        : route('patients.records.expectation-options.store', $patient);
    $allowAddImagingOptions = ! $isPatientCreate;

    $legacyMedicalHistory = $recordModel?->field('medical_history') ?? ($patientModel?->screening('medical_history'));
    $legacyClinicalAud = $recordModel?->field('clinical_aud') ?? ($patientModel?->screening('clinical_aud'));
    $legacyClinicalSpeech = $recordModel?->field('clinical_speech') ?? ($patientModel?->screening('clinical_speech'));
    $legacyExpectations = $recordModel?->field('expectations_post_ci') ?? ($patientModel?->screening('expectations_post_ci'));
    $legacyAudiologyLink = $recordModel?->field('audiology_link') ?? ($patientModel?->screening('audiology_link'));
    $legacyDeafnessAge = $recordModel?->field('deafness_age') ?? ($patientModel?->screening('deafness_age'));

    $physicianAssessment = old('field_physician_assessment', $recordModel?->field('physician_assessment'));
    $imagingFindings = old('field_imaging_findings', $recordModel?->field('imaging_findings') ?? ($patientModel?->screening('imaging_findings')));
    $audiologyDecision = old('field_audiology_decision', $recordModel?->field('audiology_decision'));
    $speechAssessment = old('field_speech_assessment', $recordModel?->field('speech_assessment'));

    $imagingDef = $stageFields['imaging_findings'] ?? [];
    $speechDef = $stageFields['speech_assessment'] ?? [];
@endphp

<div class="pre-operation-stage-fields"
     data-pre-op-fields
     data-clinical-select-url="{{ $clinicalSelectOptionUrl }}"
     data-ct-option-url="{{ $ctOptionUrl }}"
     data-mri-option-url="{{ $mriOptionUrl }}"
     data-expectation-option-url="{{ $expectationOptionUrl }}">

    <div class="follow-up-form-header clinical-phase-panel mb-3" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#FFF2CC' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#FFD966' }};">
        <div class="follow-up-form-header__inner">
            <h6 class="mb-0 fw-semibold">
                <i class="ti ti-clipboard-list me-2"></i>
                {{ __('workflow.title') }} — {{ __('workflow.phases.pre_op') }}
            </h6>
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#FFF2CC' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#FFD966' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.pre_op.cards.physician_assessment') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-pre-op-physician-assessment-input
                name-prefix="field_physician_assessment"
                :saved-value="$physicianAssessment"
                :legacy-medical-history="$legacyMedicalHistory"
                :clinical-select-option-url="$clinicalSelectOptionUrl"
            />

            <div class="pre-op-section-divider pt-3 mt-2">
                <label class="form-label fw-semibold small mb-2">{{ __('workflow.fields.imaging_findings') }}</label>
                <x-imaging-findings-input
                    name-prefix="field_imaging_findings"
                    :saved-value="$imagingFindings"
                    :ct-options="$imagingDef['ct_options'] ?? []"
                    :mri-options="$imagingDef['mri_options'] ?? []"
                    :allow-add-options="$allowAddImagingOptions"
                    :ct-add-url="$ctOptionUrl"
                    :mri-add-url="$mriOptionUrl"
                />
            </div>
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#FFF2CC' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#FFD966' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.pre_op.cards.audiology_decision') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-pre-op-audiology-decision-input
                name-prefix="field_audiology_decision"
                :saved-value="$audiologyDecision"
                :legacy-clinical-aud="$legacyClinicalAud"
                :legacy-audiology-link="$legacyAudiologyLink"
                :legacy-deafness-age="$legacyDeafnessAge"
                :clinical-select-option-url="$clinicalSelectOptionUrl"
            />
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#FFF2CC' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#FFD966' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.pre_op.cards.speech_assessment') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-pre-op-speech-assessment-input
                name-prefix="field_speech_assessment"
                :saved-value="$speechAssessment"
                :legacy-clinical-speech="$legacyClinicalSpeech"
                :legacy-expectations="$legacyExpectations"
                :expectation-options="$speechDef['expectation_options'] ?? []"
                :clinical-select-option-url="$clinicalSelectOptionUrl"
                :expectation-option-url="$expectationOptionUrl"
            />
        </div>
    </div>
</div>
