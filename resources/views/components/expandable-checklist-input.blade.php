@props([
    'namePrefix',
    'savedValue' => null,
    'options' => [],
    'allowAdd' => true,
    'fieldDefinition' => [],
])

@php
    $fieldDefinition = is_array($fieldDefinition) ? $fieldDefinition : [];
    $data = \App\Support\ScreeningFieldSupport::resolveExpandableChecklistForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue,
        $options,
        $fieldDefinition
    );
    $selected = $data['selected'];
    $custom = $data['custom'];
@endphp

<div class="clinical-expandable-checklist" data-expandable-checklist data-name-prefix="{{ $namePrefix }}">
    <div class="d-flex flex-column gap-2 mb-2">
        @foreach($options as $code => $label)
            <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="{{ $namePrefix }}[selected][]"
                       id="{{ $namePrefix }}_{{ $code }}"
                       value="{{ $code }}"
                       @checked(collect($selected)->contains(fn ($value): bool => (string) $value === (string) $code))>
                <label class="form-check-label small" for="{{ $namePrefix }}_{{ $code }}">{{ $label }}</label>
            </div>
        @endforeach
    </div>

    @if($allowAdd)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-semibold text-muted">{{ __('workflow.fields.expandable_checklist_custom') }}</span>
            <button type="button" class="btn btn-outline-primary btn-sm" data-add-checklist-option>
                <i class="ti ti-plus me-1"></i>{{ __('workflow.fields.expandable_checklist_add_option') }}
            </button>
        </div>
    @endif

    <div class="d-flex flex-column gap-2" data-checklist-custom-body>
        @foreach($custom as $index => $text)
            <div class="input-group input-group-sm" data-checklist-custom-row>
                <input type="text"
                       name="{{ $namePrefix }}[custom][]"
                       class="form-control"
                       value="{{ $text }}"
                       placeholder="{{ __('workflow.fields.expandable_checklist_custom_placeholder') }}">
                <button type="button" class="btn btn-outline-danger" data-remove-checklist-option aria-label="{{ __('common.delete') }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        @endforeach
    </div>
</div>
