@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = array_merge(
        \App\Support\FollowUpFieldSupport::defaultSpeechAssessment(),
        \App\Support\FollowUpFieldSupport::normalizeSpeechAssessment($formValue)
    );
    $groups = \App\Support\FollowUpFieldSupport::speechAssessmentOptionGroups();
    $fields = [
        'communication_mood' => __('workflow.follow_up.fields.communication_mood'),
        'true_word' => __('workflow.follow_up.fields.true_word'),
        'phrases' => __('workflow.follow_up.fields.phrases'),
    ];
@endphp

<div class="row g-3 follow-up-speech-assessment">
    @foreach($fields as $fieldKey => $fieldLabel)
        <div class="col-md-4">
            <div class="follow-up-speech-field">
                <label class="form-label fw-semibold small mb-1">{{ $fieldLabel }}</label>
                <select name="{{ $namePrefix }}[{{ $fieldKey }}]" class="form-select">
                <option value="">— {{ __('common.select') }} —</option>
                @foreach($groups[$fieldKey] ?? [] as $code => $label)
                    <option value="{{ $code }}" @selected((string) ($data[$fieldKey] ?? '') === (string) $code)>{{ $label }}</option>
                @endforeach
            </select>
            </div>
        </div>
    @endforeach
</div>
