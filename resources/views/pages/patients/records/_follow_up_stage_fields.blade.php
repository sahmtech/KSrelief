@include('pages.patients.partials.clinical-fallback-styles')

@php
    $phaseStyle = config('patient_clinical.phases.follow_up', []);
    $showTemplateActions = ! isset($record) && ($enableFollowUpTemplateActions ?? false);

    $clinicalAssessment = old(
        'field_clinical_assessment',
        isset($record)
            ? $record->field('clinical_assessment')
            : \App\Support\FollowUpFieldSupport::defaultClinicalAssessment()
    );
    $audiologyAssessment = old(
        'field_audiology_assessment',
        isset($record)
            ? $record->field('audiology_assessment')
            : \App\Support\FollowUpFieldSupport::defaultAudiologyAssessment()
    );
    $speechAssessment = old(
        'field_speech_assessment',
        isset($record)
            ? $record->field('speech_assessment')
            : \App\Support\FollowUpFieldSupport::defaultSpeechAssessment()
    );
    $followUpNotes = old(
        'field_follow_up_notes',
        isset($record)
            ? $record->field('follow_up_notes')
            : \App\Support\FollowUpFieldSupport::defaultNotes()
    );
@endphp

<div class="follow-up-stage-fields">
    <div class="follow-up-form-header clinical-phase-panel mb-3" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#f5f3ff' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6d28d9' }};">
        <div class="follow-up-form-header__inner">
            <h6 class="mb-0 fw-semibold">
                <i class="ti ti-clipboard-list me-2"></i>
                {{ __('workflow.title') }} — {{ __('workflow.phases.follow_up') }}
            </h6>
            @if($showTemplateActions)
                <button type="button"
                        data-follow-up-load-template
                        class="btn btn-outline-primary btn-sm"
                        @disabled(!($hasFollowUpDefaults ?? false))>
                    <i class="ti ti-template me-1"></i>{{ __('workflow.follow_up.load_template') }}
                </button>
            @endif
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#f5f3ff' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6d28d9' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.follow_up.cards.clinical_assessment') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-follow-up-clinical-assessment-input
                name-prefix="field_clinical_assessment"
                :saved-value="$clinicalAssessment"
            />
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#f5f3ff' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6d28d9' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.follow_up.cards.audiology_assessment') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-follow-up-audiology-assessment-input
                name-prefix="field_audiology_assessment"
                :saved-value="$audiologyAssessment"
            />
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#f5f3ff' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6d28d9' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.follow_up.cards.speech_assessment') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-follow-up-speech-assessment-input
                name-prefix="field_speech_assessment"
                :saved-value="$speechAssessment"
            />
        </div>
    </div>

    <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#f5f3ff' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6d28d9' }};">
        <div class="clinical-phase-panel__header">
            <h6 class="mb-0 fw-semibold">{{ __('workflow.follow_up.cards.notes') }}</h6>
        </div>
        <div class="clinical-phase-panel__body">
            <x-follow-up-notes-input
                name-prefix="field_follow_up_notes"
                :saved-value="$followUpNotes"
            />
        </div>
    </div>
</div>
