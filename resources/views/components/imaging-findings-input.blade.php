@props([
    'namePrefix',
    'savedValue' => null,
    'ctOptions' => [],
    'mriOptions' => [],
    'allowAddOptions' => false,
    'ctAddUrl' => '',
    'mriAddUrl' => '',
])

@php
    $data = \App\Support\ScreeningFieldSupport::resolveImagingFindingsForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue
    );
    $ears = ['right', 'left'];
@endphp

<div class="clinical-imaging-findings row g-3" data-name-prefix="{{ $namePrefix }}">
    @foreach($ears as $ear)
        @php
            $earData = $data[$ear] ?? ['ct' => [], 'mri' => [], 'ct_drive_link' => '', 'mri_drive_link' => ''];
            $selectedCt = $earData['ct'] ?? [];
            $selectedMri = $earData['mri'] ?? [];
            $ctDriveLink = $earData['ct_drive_link'] ?? '';
            $mriDriveLink = $earData['mri_drive_link'] ?? '';
        @endphp
        <div class="col-md-6">
            <div class="border rounded p-3 h-100 bg-white">
                <h6 class="small fw-semibold mb-3">{{ __('workflow.fields.imaging_ear_'.$ear) }}</h6>

                <div class="mb-3" data-imaging-option-list data-imaging-type="ct" data-ear="{{ $ear }}">
                    <label class="form-label fw-semibold small mb-2">{{ __('workflow.fields.ct_findings') }}</label>
                    <div class="d-flex flex-column gap-1" data-imaging-options-body>
                        @forelse($ctOptions as $optionId => $optionLabel)
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="{{ $namePrefix }}[{{ $ear }}][ct][]"
                                       id="{{ $namePrefix }}_{{ $ear }}_ct_{{ $optionId }}"
                                       value="{{ $optionId }}"
                                       @checked(in_array((int) $optionId, $selectedCt, true))>
                                <label class="form-check-label small" for="{{ $namePrefix }}_{{ $ear }}_ct_{{ $optionId }}">{{ $optionLabel }}</label>
                            </div>
                        @empty
                            <span class="text-muted small">{{ __('workflow.fields.imaging_no_ct_options') }}</span>
                        @endforelse
                    </div>
                    @if($allowAddOptions && filled($ctAddUrl))
                        <div class="input-group input-group-sm mt-2">
                            <input type="text"
                                   class="form-control"
                                   data-imaging-option-input
                                   placeholder="{{ __('workflow.pre_op.add_option_placeholder') }}">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-add-imaging-option
                                    data-add-url="{{ $ctAddUrl }}">
                                <i class="ti ti-plus me-1"></i>{{ __('workflow.pre_op.add_option') }}
                            </button>
                        </div>
                    @endif
                    <div class="mt-2">
                        <label class="form-label small text-muted mb-1">{{ __('workflow.fields.imaging_ct_drive_link') }}</label>
                        <input type="url"
                               name="{{ $namePrefix }}[{{ $ear }}][ct_drive_link]"
                               class="form-control form-control-sm"
                               value="{{ $ctDriveLink }}"
                               placeholder="{{ __('workflow.links.drive_placeholder') }}">
                    </div>
                </div>

                <div data-imaging-option-list data-imaging-type="mri" data-ear="{{ $ear }}">
                    <label class="form-label fw-semibold small mb-2">{{ __('workflow.fields.mri_findings') }}</label>
                    <div class="d-flex flex-column gap-1" data-imaging-options-body>
                        @forelse($mriOptions as $optionId => $optionLabel)
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="{{ $namePrefix }}[{{ $ear }}][mri][]"
                                       id="{{ $namePrefix }}_{{ $ear }}_mri_{{ $optionId }}"
                                       value="{{ $optionId }}"
                                       @checked(in_array((int) $optionId, $selectedMri, true))>
                                <label class="form-check-label small" for="{{ $namePrefix }}_{{ $ear }}_mri_{{ $optionId }}">{{ $optionLabel }}</label>
                            </div>
                        @empty
                            <span class="text-muted small">{{ __('workflow.fields.imaging_no_mri_options') }}</span>
                        @endforelse
                    </div>
                    @if($allowAddOptions && filled($mriAddUrl))
                        <div class="input-group input-group-sm mt-2">
                            <input type="text"
                                   class="form-control"
                                   data-imaging-option-input
                                   placeholder="{{ __('workflow.pre_op.add_option_placeholder') }}">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-add-imaging-option
                                    data-add-url="{{ $mriAddUrl }}">
                                <i class="ti ti-plus me-1"></i>{{ __('workflow.pre_op.add_option') }}
                            </button>
                        </div>
                    @endif
                    <div class="mt-2">
                        <label class="form-label small text-muted mb-1">{{ __('workflow.fields.imaging_mri_drive_link') }}</label>
                        <input type="url"
                               name="{{ $namePrefix }}[{{ $ear }}][mri_drive_link]"
                               class="form-control form-control-sm"
                               value="{{ $mriDriveLink }}"
                               placeholder="{{ __('workflow.links.drive_placeholder') }}">
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
