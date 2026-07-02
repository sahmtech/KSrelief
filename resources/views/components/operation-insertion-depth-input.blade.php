@props([
    'namePrefix',
    'savedValue' => null,
    'legacyNote' => null,
    'clinicalSelectOptionUrl' => '',
    'required' => false,
])

@php
    $formValue = is_array(old($namePrefix)) ? old($namePrefix) : $savedValue;
    $data = \App\Support\OperationFieldSupport::resolveInsertionDepthForForm($formValue, $legacyNote);
    $options = \App\Support\OperationFieldSupport::insertionDepthOptions();
    $isPartial = ($data['selection'] ?? '') === 'partial_insertion';
@endphp

<div class="operation-insertion-depth" data-operation-insertion-depth>
    <div class="inline-clinical-select"
         data-inline-select
         data-category="operation_insertion_depth"
         data-add-url="{{ $clinicalSelectOptionUrl }}">
        <label class="form-label fw-semibold small mb-1">
            {{ __('workflow.operation.fields.insertion_depth') }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
        <select name="{{ $namePrefix }}[selection]"
                class="form-select"
                data-inline-select-control
                data-insertion-depth-select
                @required($required)>
            <option value="">— {{ __('common.select') }} —</option>
            @foreach($options as $code => $label)
                <option value="{{ $code }}" @selected((string) ($data['selection'] ?? '') === (string) $code)>{{ $label }}</option>
            @endforeach
        </select>
        @if(filled($clinicalSelectOptionUrl))
            <div class="input-group input-group-sm mt-2">
                <input type="text"
                       class="form-control"
                       data-inline-option-input
                       placeholder="{{ __('workflow.operation.add_option_placeholder') }}">
                <button type="button" class="btn btn-outline-secondary" data-add-inline-option>
                    <i class="ti ti-plus me-1"></i>{{ __('workflow.operation.add_option') }}
                </button>
            </div>
        @endif
    </div>

    <div class="mt-2" data-insertion-depth-note @unless($isPartial) hidden @endunless>
        <label class="form-label fw-semibold small mb-1" data-insertion-depth-note-label>
            {{ __('workflow.operation.fields.insertion_depth_partial_why') }}
        </label>
        <textarea name="{{ $namePrefix }}[note]"
                  class="form-control form-control-sm"
                  rows="2"
                  placeholder="{{ __('workflow.operation.fields.insertion_depth_partial_why_placeholder') }}">{{ $data['note'] ?? '' }}</textarea>
    </div>
</div>
