@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    $speech = \App\Support\ClinicalCompositeFields::resolveSpeechForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue
    );
    $assessmentOptions = \App\Support\ClinicalCompositeFields::speechScreeningAssessmentOptions();
    $notes = $speech['notes'];
    $assessment = $speech['assessment'];
@endphp

<div class="clinical-composite clinical-composite--speech">
    <div class="mb-3">
        <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.clinical_speech_notes') }}</label>
        <textarea name="{{ $namePrefix }}[notes]" class="form-control form-control-sm" rows="3">{{ $notes }}</textarea>
    </div>

    <div>
        <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.clinical_speech_assessment') }}</label>
        <select name="{{ $namePrefix }}[assessment]" class="form-select form-select-sm">
            <option value="">— {{ __('common.select') }} —</option>
            @foreach($assessmentOptions as $code => $label)
                <option value="{{ $code }}" @selected((string) $assessment === (string) $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
