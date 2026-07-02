@props([
    'namePrefix',
    'savedValue' => null,
    'legacyImpedance' => null,
    'required' => false,
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\OperationFieldSupport::resolveAudioTestForForm($formValue, $legacyImpedance);
@endphp

<div class="operation-audio-test">
    <x-kv-metrics-input
        :name-prefix="$namePrefix"
        :saved-value="$data"
        :default-keys="\App\Support\OperationFieldSupport::AUDIO_TEST_KEYS"
        :table-title="__('workflow.operation.fields.audio_test')"
        :allow-add-rows="true"
        :required="$required"
    />
</div>
