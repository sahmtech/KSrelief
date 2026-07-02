@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\FollowUpFieldSupport::resolveClinicalAssessmentForForm($formValue);
    $groups = \App\Support\FollowUpFieldSupport::clinicalAssessmentSelectGroups();
    $fields = [
        'wound' => __('workflow.follow_up.tables.wound'),
        'implant_bed' => __('workflow.follow_up.tables.implant_bed'),
    ];
@endphp

<div class="follow-up-clinical-assessment">
    <div class="row g-3">
        @foreach($fields as $fieldKey => $label)
            <div class="col-md-6">
                <label class="form-label fw-semibold small mb-1">{{ $label }}</label>
                <select name="{{ $namePrefix }}[{{ $fieldKey }}]" class="form-select">
                    <option value="">— {{ __('common.select') }} —</option>
                    @foreach($groups[$fieldKey] ?? [] as $code => $optionLabel)
                        <option value="{{ $code }}" @selected((string) ($data[$fieldKey] ?? '') === (string) $code)>
                            {{ $optionLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</div>
