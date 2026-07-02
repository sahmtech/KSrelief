@props([
    'namePrefix',
    'savedValue' => null,
    'legacyMedicalHistory' => null,
    'clinicalSelectOptionUrl' => '',
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\PreOperationFieldSupport::resolvePhysicianAssessmentForForm($formValue, $legacyMedicalHistory);
    $groups = \App\Support\PreOperationFieldSupport::physicianAssessmentSelectGroups();
    $fields = [
        'general_condition' => ['label' => __('workflow.pre_op.fields.general_condition'), 'category' => 'pre_op_general_condition'],
        'pre_op_request' => ['label' => __('workflow.pre_op.fields.pre_op_request'), 'category' => 'pre_op_pre_op_request'],
        'clinical_decision' => ['label' => __('workflow.pre_op.fields.clinical_decision'), 'category' => 'pre_op_clinical_decision'],
    ];
@endphp

<div class="pre-op-physician-assessment">
    <div class="row g-3 mb-3">
        @foreach($fields as $fieldKey => $meta)
            <div class="col-md-4">
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

    <div>
        <label class="form-label fw-semibold small mb-1">{{ __('workflow.pre_op.fields.physician_notes') }}</label>
        <textarea name="{{ $namePrefix }}[notes]" class="form-control follow-up-notes-textarea" rows="4">{{ $data['notes'] ?? '' }}</textarea>
    </div>
</div>
