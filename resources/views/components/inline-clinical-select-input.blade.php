@props([
    'name',
    'label',
    'category',
    'options' => [],
    'value' => '',
    'addOptionUrl' => '',
    'required' => false,
])

<div class="inline-clinical-select"
     data-inline-select
     data-category="{{ $category }}"
     data-add-url="{{ $addOptionUrl }}">
    <label class="form-label fw-semibold small mb-1">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    <select name="{{ $name }}" class="form-select" data-inline-select-control @required($required)>
        <option value="">— {{ __('common.select') }} —</option>
        @foreach($options as $code => $optionLabel)
            <option value="{{ $code }}" @selected((string) $value === (string) $code)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if(filled($addOptionUrl))
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
