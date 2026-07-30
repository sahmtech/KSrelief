@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\FollowUpFieldSupport::resolveAudiologyAssessmentForForm($formValue);
@endphp

<div class="follow-up-audiology-assessment">
    <div class="mb-3">
        <x-hearing-assessment-workflow-input
            :name-prefix="$namePrefix"
            :saved-value="$data"
        />
    </div>

    <x-kv-metrics-input
        :name-prefix="$namePrefix"
        :saved-value="$data"
        :default-keys="\App\Support\FollowUpFieldSupport::AUDIOLOGY_DEFAULT_KEYS"
        :table-title="__('workflow.hearing_assessment.metrics_table')"
        :allow-add-rows="true"
    />
</div>
