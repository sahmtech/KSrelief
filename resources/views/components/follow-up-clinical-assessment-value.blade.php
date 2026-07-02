@props(['value' => null])

@php
    $data = \App\Support\FollowUpFieldSupport::resolveClinicalAssessmentForForm($value);
    $groups = \App\Support\FollowUpFieldSupport::clinicalAssessmentSelectGroups();
    $fields = [
        'wound' => __('workflow.follow_up.tables.wound'),
        'implant_bed' => __('workflow.follow_up.tables.implant_bed'),
    ];
@endphp

<div class="follow-up-clinical-assessment-value">
    @foreach($fields as $fieldKey => $label)
        @if(filled($data[$fieldKey] ?? null))
            <div class="mb-1">
                <span class="small fw-semibold text-muted">{{ $label }}:</span>
                {{ $groups[$fieldKey][$data[$fieldKey]] ?? $data[$fieldKey] }}
            </div>
        @endif
    @endforeach
    @if(! filled($data['wound'] ?? null) && ! filled($data['implant_bed'] ?? null))
        <span>—</span>
    @endif
</div>
