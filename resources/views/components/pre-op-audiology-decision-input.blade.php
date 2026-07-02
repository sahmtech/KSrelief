@props([
    'namePrefix',
    'savedValue' => null,
    'legacyClinicalAud' => null,
    'legacyAudiologyLink' => null,
    'legacyDeafnessAge' => null,
    'clinicalSelectOptionUrl' => '',
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\PreOperationFieldSupport::resolveAudiologyDecisionForForm(
        $formValue,
        $legacyClinicalAud,
        $legacyAudiologyLink,
        $legacyDeafnessAge
    );
    $statusOptions = \App\Support\PreOperationFieldSupport::selectOptions('pre_op_audiology_status');
    $decisionOptions = \App\Support\PreOperationFieldSupport::selectOptions('pre_op_audiology_decision');
@endphp

<div class="pre-op-audiology-decision">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <x-inline-clinical-select-input
                :name="$namePrefix.'[status]'"
                :label="__('workflow.pre_op.fields.audiology_status')"
                category="pre_op_audiology_status"
                :options="$statusOptions"
                :value="$data['status'] ?? ''"
                :add-option-url="$clinicalSelectOptionUrl"
            />
        </div>
        <div class="col-md-6">
            <x-inline-clinical-select-input
                :name="$namePrefix.'[decision]'"
                :label="__('workflow.pre_op.fields.audiology_decision')"
                category="pre_op_audiology_decision"
                :options="$decisionOptions"
                :value="$data['decision'] ?? ''"
                :add-option-url="$clinicalSelectOptionUrl"
            />
        </div>
    </div>

    <div class="mb-3">
        <div class="small fw-semibold text-muted mb-2">{{ __('workflow.pre_op.fields.hearing_assessment') }}</div>
        <x-kv-metrics-input
            :name-prefix="$namePrefix"
            :saved-value="['metrics' => $data['metrics'] ?? []]"
            :default-keys="\App\Support\PreOperationFieldSupport::HEARING_ASSESSMENT_KEYS"
            :allow-add-rows="true"
        />
    </div>

    <div>
        <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.audiology_link') }}</label>
        <input type="url"
               name="{{ $namePrefix }}[audiology_link]"
               class="form-control"
               value="{{ $data['audiology_link'] ?? '' }}"
               placeholder="{{ __('workflow.links.drive_placeholder') }}">
    </div>
</div>
