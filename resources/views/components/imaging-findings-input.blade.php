@props([
    'namePrefix',
    'savedValue' => null,
    'ctOptions' => [],
    'mriOptions' => [],
])

@php
    $data = \App\Support\ScreeningFieldSupport::resolveImagingFindingsForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue
    );
    $ears = ['right', 'left'];
@endphp

<div class="clinical-imaging-findings row g-3">
    @foreach($ears as $ear)
        @php
            $earData = $data[$ear] ?? ['ct' => [], 'mri' => []];
            $selectedCt = $earData['ct'] ?? [];
            $selectedMri = $earData['mri'] ?? [];
        @endphp
        <div class="col-md-6">
            <div class="border rounded p-3 h-100 bg-white">
                <h6 class="small fw-semibold mb-3">{{ __('workflow.fields.imaging_ear_'.$ear) }}</h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold small mb-2">{{ __('workflow.fields.ct_findings') }}</label>
                    <div class="d-flex flex-column gap-1">
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
                </div>

                <div>
                    <label class="form-label fw-semibold small mb-2">{{ __('workflow.fields.mri_findings') }}</label>
                    <div class="d-flex flex-column gap-1">
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
                </div>
            </div>
        </div>
    @endforeach
</div>
