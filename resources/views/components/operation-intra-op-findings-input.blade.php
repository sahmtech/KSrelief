@props([
    'namePrefix',
    'savedValue' => null,
    'legacyValue' => null,
    'clinicalSelectOptionUrl' => '',
    'required' => false,
])

@php
    $value = old($namePrefix, $savedValue ?? $legacyValue);
    $options = \App\Support\OperationFieldSupport::intraOpFindingsOptions();
@endphp

<x-inline-clinical-select-input
    :name="$namePrefix"
    :label="__('workflow.fields.intra_op_findings')"
    category="operation_intra_op_findings"
    :options="$options"
    :value="$value"
    :add-option-url="$clinicalSelectOptionUrl"
    :required="$required"
/>
