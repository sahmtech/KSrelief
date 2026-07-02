@include('pages.patients.partials.clinical-fallback-styles')

@php
    $phaseStyle = config('patient_clinical.phases.post_op', []);
    $recordModel = $record ?? null;
    $clinicalSelectOptionUrl = route('patients.records.clinical-select-options.store', $patient);

    $legacyClinicalAud = $recordModel?->field('clinical_aud');

    $physicianAssessment = old('field_physician_assessment', $recordModel?->field('physician_assessment'));
    $clinicalAud = old('field_clinical_aud', $recordModel?->field('clinical_aud'));
    $counselling = old('field_counselling', $recordModel?->field('counselling'));
    $postOpNotes = old('field_post_op_notes', $recordModel?->field('post_op_notes'));
@endphp

<div class="post-operation-stage-fields"
     data-post-op-fields
     data-clinical-select-url="{{ $clinicalSelectOptionUrl }}">

    <div class="follow-up-form-header clinical-phase-panel mb-3" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#9FC5E8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#3C78D8' }};">
        <div class="follow-up-form-header__inner">
            <h6 class="mb-0 fw-semibold">
                <i class="ti ti-clipboard-list me-2"></i>
                {{ __('workflow.title') }} — {{ __('workflow.post_op.title') }}
            </h6>
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#9FC5E8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#3C78D8' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.post_op.cards.physician_assessment') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-post-op-physician-assessment-input
                name-prefix="field_physician_assessment"
                :saved-value="$physicianAssessment"
                :clinical-select-option-url="$clinicalSelectOptionUrl"
            />
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#9FC5E8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#3C78D8' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.post_op.cards.clinical_aud') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-post-op-clinical-aud-input
                name-prefix="field_clinical_aud"
                :saved-value="$clinicalAud"
                :legacy-clinical-aud="$legacyClinicalAud"
            />
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#9FC5E8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#3C78D8' }};">
        <div class="clinical-phase-panel__body py-3">
            <x-yes-no-input
                name-prefix="field_counselling"
                :saved-value="$counselling"
                :label="__('workflow.post_op.fields.counselling')"
            />
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#9FC5E8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#3C78D8' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.post_op.cards.notes') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-post-op-notes-input
                name-prefix="field_post_op_notes"
                :saved-value="$postOpNotes"
            />
        </div>
    </div>
</div>
