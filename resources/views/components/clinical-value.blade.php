@props([
    'value' => null,
    'type' => null,
    'linkLabel' => null,
    'fieldKey' => null,
    'fieldDefinition' => [],
])

@php
    $operationTypes = ['company_select', 'electrode_select', 'insertion_approach_select'];
    $isOperationField = in_array($type, $operationTypes, true);
    $fieldDefinition = is_array($fieldDefinition) ? $fieldDefinition : [];
    $specializedTypes = [
        'clinical_aud',
        'clinical_speech_followup',
        'clinical_speech',
        'expandable_checklist',
        'medical_history_screening',
        'imaging_findings',
        'follow_up_clinical_assessment',
        'follow_up_audiology_assessment',
        'follow_up_speech_assessment',
        'follow_up_notes',
        'pre_op_physician_assessment',
        'pre_op_audiology_decision',
        'pre_op_speech_assessment',
        'operation_insertion_depth',
        'operation_audio_test',
        'operation_intra_op_findings',
        'post_op_physician_assessment',
        'post_op_clinical_aud',
        'post_op_notes',
        'yes_no',
        'select',
        'member_select',
    ];
    $presented = null;

    if (
        ! $isOperationField
        && ! in_array($type, $specializedTypes, true)
        && ! is_array($value)
    ) {
        $presented = \App\Support\ClinicalValuePresenter::present($value, $type, $linkLabel);
    }

    if ($isOperationField) {
        $resolved = \App\Support\OperationFieldResolver::resolve($fieldKey ?? '', $value, ['type' => $type]);
        $displayText = $resolved['text'];
        $displayColor = $resolved['color'];
    }
@endphp

@if($type === 'clinical_aud')
    <x-clinical-aud-value :value="$value" :field-definition="$fieldDefinition" />
@elseif($type === 'clinical_speech_followup')
    <x-clinical-speech-followup-value :value="$value" />
@elseif($type === 'clinical_speech')
    <x-clinical-speech-value :value="$value" />
@elseif($type === 'expandable_checklist')
    <x-expandable-checklist-value :value="$value" :field-definition="$fieldDefinition" />
@elseif($type === 'medical_history_screening')
    <x-medical-history-screening-value :value="$value" />
@elseif($type === 'imaging_findings')
    <x-imaging-findings-value :value="$value" />
@elseif($type === 'follow_up_clinical_assessment')
    <x-follow-up-clinical-assessment-value :value="$value" />
@elseif($type === 'follow_up_audiology_assessment')
    <x-follow-up-audiology-assessment-value :value="$value" />
@elseif($type === 'follow_up_speech_assessment')
    <x-follow-up-speech-assessment-value :value="$value" />
@elseif($type === 'follow_up_notes')
    <x-follow-up-notes-value :value="$value" />
@elseif($type === 'pre_op_physician_assessment')
    <x-pre-op-physician-assessment-value :value="$value" />
@elseif($type === 'pre_op_audiology_decision')
    <x-pre-op-audiology-decision-value :value="$value" />
@elseif($type === 'pre_op_speech_assessment')
    <x-pre-op-speech-assessment-value :value="$value" :field-definition="$fieldDefinition" />
@elseif($type === 'operation_insertion_depth')
    <x-operation-insertion-depth-value :value="$value" />
@elseif($type === 'operation_audio_test')
    <x-operation-audio-test-value :value="$value" />
@elseif($type === 'operation_intra_op_findings')
    <x-operation-intra-op-findings-value :value="$value" />
@elseif($type === 'post_op_physician_assessment')
    <x-post-op-physician-assessment-value :value="$value" />
@elseif($type === 'post_op_clinical_aud')
    <x-post-op-clinical-aud-value :value="$value" />
@elseif($type === 'post_op_notes')
    <x-post-op-notes-value :value="$value" />
@elseif($type === 'yes_no' || ($type === 'select' && !empty($fieldDefinition['options'])))
    <span class="text-break">{{ \App\Support\ScreeningFieldSupport::selectOptionLabel($value, $fieldDefinition) }}</span>
@elseif($type === 'member_select')
    <span class="text-break">{{ \App\Support\MedicalRecordFieldPresenter::display($fieldKey ?? '', $value, array_merge($fieldDefinition, ['type' => 'member_select'])) }}</span>
@elseif($isOperationField)
    @if(filled($displayColor))
        <span class="text-break fw-semibold" style="color: {{ $displayColor }};">{{ $displayText }}</span>
    @else
        <span class="text-break">{{ $displayText }}</span>
    @endif
@elseif($presented !== null && $presented['is_link'])
    <a
        href="{{ $presented['url'] }}"
        target="_blank"
        rel="noopener noreferrer"
        class="clinical-link clinical-link--{{ $presented['variant'] }}"
        title="{{ $presented['url'] }}"
    >
        <i class="ti ti-{{ $presented['icon'] }} clinical-link__icon"></i>
        <span class="clinical-link__label">{{ $presented['label'] }}</span>
    </a>
@elseif($presented !== null)
    <span class="text-break">{{ $presented['text'] }}</span>
@else
    <span class="text-break">{{ is_scalar($value) ? (string) $value : '—' }}</span>
@endif
