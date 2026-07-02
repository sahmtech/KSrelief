@props(['value' => null])

@php
    $data = \App\Support\FollowUpFieldSupport::normalizeSpeechAssessment($value);
    $groups = \App\Support\FollowUpFieldSupport::speechAssessmentOptionGroups();
    $fields = [
        'communication_mood' => __('workflow.follow_up.fields.communication_mood'),
        'true_word' => __('workflow.follow_up.fields.true_word'),
        'phrases' => __('workflow.follow_up.fields.phrases'),
    ];
@endphp

<dl class="row small mb-0 follow-up-speech-assessment-value">
    @foreach($fields as $fieldKey => $fieldLabel)
        @if(filled($data[$fieldKey]))
            <dt class="col-sm-5 fw-semibold">{{ $fieldLabel }}</dt>
            <dd class="col-sm-7 mb-2">{{ $groups[$fieldKey][$data[$fieldKey]] ?? $data[$fieldKey] }}</dd>
        @endif
    @endforeach
</dl>
