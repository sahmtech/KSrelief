@props([
    'namePrefix',
    'savedValue' => null,
    'legacyClinicalAud' => null,
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\PostOperationFieldSupport::resolveClinicalAudForForm($formValue, $legacyClinicalAud);
@endphp

<div class="post-op-clinical-aud">
    <x-kv-metrics-input
        :name-prefix="$namePrefix"
        :saved-value="$data"
        :default-keys="\App\Support\PostOperationFieldSupport::CLINICAL_AUD_KEYS"
        :table-title="__('workflow.post_op.cards.clinical_aud')"
        :allow-add-rows="true"
    />
</div>
