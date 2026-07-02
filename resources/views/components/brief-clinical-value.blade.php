@props([
    'value' => null,
    'type' => null,
    'fieldKey' => null,
    'fieldDefinition' => [],
    'linkLabel' => null,
])

@php
    $fieldDefinition = is_array($fieldDefinition) ? $fieldDefinition : [];
    $type = $type ?? ($fieldDefinition['type'] ?? null);
@endphp

@if($type === 'clinical_aud')
    @php
        $data = \App\Support\ClinicalCompositeFields::normalizeAud(
            $value,
            \App\Support\ClinicalCompositeFields::metricsKeysFromDefinition($fieldDefinition),
            (bool) ($fieldDefinition['with_status'] ?? true)
        );
        $statusOptions = \App\Support\ClinicalCompositeFields::audStatusOptions();
        $parts = collect($data['metrics'])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null))
            ->map(fn (array $row): string => ($row['key'] ?? '').': '.($row['value'] ?? ''))
            ->all();
        if (($fieldDefinition['with_status'] ?? true) && filled($data['status'])) {
            $parts[] = ($statusOptions[$data['status']] ?? $data['status']);
        }
    @endphp
    @if($parts !== [])
        <span class="clinical-brief-inline">{{ implode(' · ', $parts) }}</span>
    @else
        <span class="text-muted">—</span>
    @endif

@elseif($type === 'clinical_speech' || $type === 'clinical_speech_followup')
    @php
        if ($type === 'clinical_speech_followup') {
            $data = \App\Support\ClinicalCompositeFields::normalizeSpeechFollowup(
                $value,
                config('patient_clinical.clinical_speech_follow_up_keys', ['Cap', 'SIR'])
            );
            $metricParts = collect($data['metrics'])
                ->filter(fn (array $row): bool => filled($row['value'] ?? null))
                ->map(fn (array $row): string => ($row['key'] ?? '').': '.($row['value'] ?? ''))
                ->all();
            $assessment = $data['assessment'] ?? null;
        } else {
            $data = \App\Support\ClinicalCompositeFields::normalizeSpeech($value);
            $metricParts = [];
            $assessment = $data['assessment'] ?? null;
        }
        $note = filled($data['notes'] ?? null) ? \Illuminate\Support\Str::limit((string) $data['notes'], 72) : null;
    @endphp
    <span class="clinical-brief-inline">
        @if($note)
            {{ $note }}
            @if($assessment || $metricParts !== []) · @endif
        @endif
        @if($metricParts !== [])
            {{ implode(' · ', $metricParts) }}
            @if($assessment) · @endif
        @endif
        @if($assessment)
            <span class="badge bg-light text-dark border fw-normal">{{ \App\Support\ClinicalCompositeFields::speechAssessmentLabel((string) $assessment) }}</span>
        @endif
        @if(! $note && $metricParts === [] && ! $assessment)
            <span class="text-muted">—</span>
        @endif
    </span>

@elseif($type === 'imaging_findings')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \Illuminate\Support\Str::limit(\App\Support\ScreeningFieldSupport::presentImagingFindings($value), 120) }}</span>

@elseif($type === 'expandable_checklist')
    <span class="clinical-brief-inline">{{ \Illuminate\Support\Str::limit(\App\Support\ScreeningFieldSupport::presentExpandableChecklist($value, $fieldDefinition), 100) }}</span>

@elseif($type === 'medical_history_screening')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \Illuminate\Support\Str::limit(\App\Support\ScreeningFieldSupport::presentMedicalHistoryScreening($value), 100) }}</span>

@elseif($type === 'follow_up_clinical_assessment')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\FollowUpFieldSupport::presentClinicalAssessment($value) }}</span>
@elseif($type === 'follow_up_audiology_assessment')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\FollowUpFieldSupport::presentAudiologyAssessment($value) }}</span>
@elseif($type === 'follow_up_speech_assessment')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\FollowUpFieldSupport::presentSpeechAssessment($value) }}</span>
@elseif($type === 'follow_up_notes')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\FollowUpFieldSupport::presentNotes($value) }}</span>
@elseif($type === 'pre_op_physician_assessment')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\PreOperationFieldSupport::presentPhysicianAssessment($value) }}</span>
@elseif($type === 'pre_op_audiology_decision')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\PreOperationFieldSupport::presentAudiologyDecision($value) }}</span>
@elseif($type === 'pre_op_speech_assessment')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\PreOperationFieldSupport::presentSpeechAssessment($value, $fieldDefinition['expectation_options'] ?? []) }}</span>
@elseif($type === 'post_op_physician_assessment')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\PostOperationFieldSupport::presentPhysicianAssessment($value) }}</span>
@elseif($type === 'post_op_clinical_aud')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\PostOperationFieldSupport::presentClinicalAud($value) }}</span>
@elseif($type === 'post_op_notes')
    <span class="clinical-brief-inline clinical-brief-inline--multiline">{{ \App\Support\PostOperationFieldSupport::presentNotes($value) }}</span>
@elseif($type === 'yes_no' || ($type === 'select' && ! empty($fieldDefinition['options'])))
    <span class="clinical-brief-inline">{{ \App\Support\ScreeningFieldSupport::selectOptionLabel($value, $fieldDefinition) }}</span>

@elseif($type === 'member_select')
    <span class="clinical-brief-inline">{{ \App\Support\MedicalRecordFieldPresenter::display($fieldKey ?? '', $value, array_merge($fieldDefinition, ['type' => 'member_select'])) }}</span>

@elseif(in_array($type, ['company_select', 'electrode_select', 'insertion_approach_select'], true))
    @php $resolved = \App\Support\OperationFieldResolver::resolve($fieldKey ?? '', $value, ['type' => $type]); @endphp
    @if(filled($resolved['color']))
        <span class="clinical-brief-inline fw-semibold" style="color: {{ $resolved['color'] }};">{{ $resolved['text'] }}</span>
    @else
        <span class="clinical-brief-inline">{{ $resolved['text'] }}</span>
    @endif

@else
    <x-clinical-value
        :value="$value"
        :type="$type"
        :field-key="$fieldKey"
        :field-definition="$fieldDefinition"
        :link-label="$linkLabel"
    />
@endif
