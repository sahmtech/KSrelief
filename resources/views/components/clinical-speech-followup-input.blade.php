@props([
    'namePrefix',
    'savedValue' => null,
    'allowAddRows' => true,
])

@php
    $keys = config('patient_clinical.clinical_speech_follow_up_keys', ['Cap', 'SIR']);
    $data = \App\Support\ClinicalCompositeFields::resolveSpeechFollowupForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue,
        $keys
    );
    $metrics = $data['metrics'];
    $assessment = $data['assessment'];
    $assessmentOptions = \App\Support\ClinicalCompositeFields::speechAssessmentOptions();
@endphp

<div class="clinical-composite clinical-composite--speech-followup">
    <x-clinical-aud-input
        :name-prefix="$namePrefix"
        :saved-value="['metrics' => $metrics]"
        :metrics-keys="$keys"
        :with-status="false"
        :allow-add-rows="$allowAddRows"
    />

    <div class="mt-3">
        <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.clinical_speech_assessment') }}</label>
        <select name="{{ $namePrefix }}[assessment]" class="form-select form-select-sm">
            <option value="">— {{ __('common.select') }} —</option>
            @foreach($assessmentOptions as $code => $label)
                <option value="{{ $code }}" @selected((string) $assessment === (string) $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
