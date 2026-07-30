@props([
    'namePrefix',
    'savedValue' => null,
    'legacyClinicalSpeech' => null,
    'legacyExpectations' => null,
    'expectationOptions' => [],
    'clinicalSelectOptionUrl' => '',
    'expectationOptionUrl' => '',
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\PreOperationFieldSupport::resolveSpeechAssessmentForForm(
        $formValue,
        $legacyClinicalSpeech,
        $legacyExpectations
    );
    $selectFields = [
        'communication_mood' => ['label' => __('workflow.pre_op.fields.communication_mood'), 'category' => 'pre_op_speech_communication_mood'],
        'iq' => ['label' => __('workflow.pre_op.fields.iq'), 'category' => 'pre_op_speech_iq'],
        'cognitive_function' => ['label' => __('workflow.pre_op.fields.cognitive_function'), 'category' => 'pre_op_speech_cognitive_function'],
        'true_word' => ['label' => __('workflow.pre_op.fields.true_word'), 'category' => 'pre_op_speech_true_word'],
        'phrases' => ['label' => __('workflow.pre_op.fields.phrases'), 'category' => 'pre_op_speech_phrases'],
        'assessment' => ['label' => __('workflow.pre_op.fields.assessment'), 'category' => 'pre_op_speech_assessment'],
        'speech_decision' => ['label' => __('workflow.pre_op.fields.speech_decision'), 'category' => 'pre_op_speech_decision'],
    ];
@endphp

<div class="pre-op-speech-assessment">
    <div class="row g-3">
        @foreach($selectFields as $fieldKey => $meta)
            <div class="col-md-4">
                <x-inline-clinical-select-input
                    :name="$namePrefix.'['.$fieldKey.']'"
                    :label="$meta['label']"
                    :category="$meta['category']"
                    :options="\App\Support\PreOperationFieldSupport::selectOptions($meta['category'])"
                    :value="$data[$fieldKey] ?? ''"
                    :add-option-url="$clinicalSelectOptionUrl"
                />
            </div>
        @endforeach

        <div class="col-md-4">
            <div class="inline-clinical-select"
                 data-inline-select
                 data-category="expectation_post_ci"
                 data-add-url="{{ $expectationOptionUrl }}">
                <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.expectations_post_ci') }}</label>
                <select name="{{ $namePrefix }}[expectations_post_ci]" class="form-select" data-inline-select-control>
                    <option value="">— {{ __('common.select') }} —</option>
                    @foreach($expectationOptions as $optionId => $optionLabel)
                        <option value="{{ $optionId }}" @selected((string) ($data['expectations_post_ci'] ?? '') === (string) $optionId)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                @if(filled($expectationOptionUrl))
                    <div class="input-group input-group-sm mt-2">
                        <input type="text"
                               class="form-control"
                               data-inline-option-input
                               placeholder="{{ __('workflow.pre_op.add_option_placeholder') }}">
                        <button type="button" class="btn btn-outline-secondary" data-add-inline-option>
                            <i class="ti ti-plus me-1"></i>{{ __('workflow.pre_op.add_option') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <label class="form-label fw-semibold small mb-1">{{ __('workflow.pre_op.fields.cap') }}</label>
            <input type="text"
                   name="{{ $namePrefix }}[cap]"
                   class="form-control"
                   value="{{ $data['cap'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small mb-1">{{ __('workflow.pre_op.fields.sir') }}</label>
            <input type="text"
                   name="{{ $namePrefix }}[sir]"
                   class="form-control"
                   value="{{ $data['sir'] ?? '' }}">
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label fw-semibold small mb-1">{{ __('workflow.pre_op.fields.speech_notes') }}</label>
        <textarea name="{{ $namePrefix }}[notes]" class="form-control follow-up-notes-textarea" rows="4">{{ $data['notes'] ?? '' }}</textarea>
    </div>
</div>
