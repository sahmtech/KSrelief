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

    if ($isOperationField) {
        $resolved = \App\Support\OperationFieldResolver::resolve($fieldKey ?? '', $value, ['type' => $type]);
        $displayText = $resolved['text'];
        $displayColor = $resolved['color'];
    } else {
        $presented = \App\Support\ClinicalValuePresenter::present($value, $type, $linkLabel);
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
@elseif($type === 'select' && !empty($fieldDefinition['options']))
    <span class="text-break">{{ \App\Support\ScreeningFieldSupport::selectOptionLabel($value, $fieldDefinition) }}</span>
@elseif($isOperationField)
    @if(filled($displayColor))
        <span class="text-break fw-semibold" style="color: {{ $displayColor }};">{{ $displayText }}</span>
    @else
        <span class="text-break">{{ $displayText }}</span>
    @endif
@elseif($presented['is_link'])
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
@else
    <span class="text-break">{{ $presented['text'] }}</span>
@endif
