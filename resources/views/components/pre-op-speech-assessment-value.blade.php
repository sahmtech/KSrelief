@props([
    'value' => null,
    'fieldDefinition' => [],
])

@php
    $expectationOptions = is_array($fieldDefinition['expectation_options'] ?? null)
        ? $fieldDefinition['expectation_options']
        : [];
@endphp

<span class="text-break" style="white-space: pre-line;">{{ \App\Support\PreOperationFieldSupport::presentSpeechAssessment($value, $expectationOptions) }}</span>
