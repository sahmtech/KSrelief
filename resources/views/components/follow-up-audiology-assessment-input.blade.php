@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\FollowUpFieldSupport::resolveAudiologyAssessmentForForm($formValue);
@endphp

<div class="follow-up-audiology-assessment">
    <x-kv-metrics-input
        :name-prefix="$namePrefix"
        :saved-value="$data"
        :default-keys="\App\Support\FollowUpFieldSupport::AUDIOLOGY_DEFAULT_KEYS"
        :table-title="__('workflow.follow_up.cards.audiology_assessment')"
        :allow-add-rows="true"
    />
</div>
