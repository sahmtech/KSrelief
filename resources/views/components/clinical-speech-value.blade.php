@props(['value' => null])

@php
    $data = \App\Support\ClinicalCompositeFields::normalizeSpeech($value);
@endphp

<div class="clinical-composite-display">
    @if(filled($data['notes']))
        <p class="mb-2 text-break">{{ $data['notes'] }}</p>
    @endif

    @if(filled($data['assessment']))
        <div class="small">
            <span class="text-muted fw-semibold">{{ __('workflow.fields.clinical_speech_assessment') }}:</span>
            <span class="badge bg-light text-dark border">{{ \App\Support\ClinicalCompositeFields::speechAssessmentLabel((string) $data['assessment']) }}</span>
        </div>
    @endif
</div>
