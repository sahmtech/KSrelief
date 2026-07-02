@props([
    'namePrefix',
    'savedValue' => null,
    'clinicalSelectOptionUrl' => '',
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\PostOperationFieldSupport::resolvePhysicianAssessmentForForm($formValue);
    $groups = \App\Support\PostOperationFieldSupport::physicianAssessmentSelectGroups();
    $fields = [
        'wound' => ['label' => __('workflow.post_op.fields.wound'), 'category' => 'post_op_wound'],
        'implant_bed' => ['label' => __('workflow.post_op.fields.implant_bed'), 'category' => 'post_op_implant_bed'],
        'facial_nerve' => ['label' => __('workflow.post_op.fields.facial_nerve'), 'category' => 'post_op_facial_nerve'],
        'post_op_xray' => ['label' => __('workflow.post_op.fields.post_op_xray'), 'category' => 'post_op_xray'],
    ];
@endphp

<div class="post-op-physician-assessment">
    <div class="row g-3">
        @foreach($fields as $fieldKey => $meta)
            <div class="col-md-6">
                <x-inline-clinical-select-input
                    :name="$namePrefix.'['.$fieldKey.']'"
                    :label="$meta['label']"
                    :category="$meta['category']"
                    :options="$groups[$fieldKey] ?? []"
                    :value="$data[$fieldKey] ?? ''"
                    :add-option-url="$clinicalSelectOptionUrl"
                />
            </div>
        @endforeach
    </div>
</div>
