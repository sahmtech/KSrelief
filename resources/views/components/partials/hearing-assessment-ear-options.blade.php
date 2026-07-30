@props([
    'type',
    'namePrefix',
    'assessment' => [],
])

@php
    use App\Support\HearingAssessmentSupport;

    $options = HearingAssessmentSupport::earOptionsForType($type);

    $selectedRight = $assessment['right'] ?? null;
    $selectedLeft = $assessment['left'] ?? null;

    if (is_array($selectedRight)) {
        $selectedRight = $selectedRight[0] ?? null;
    }

    if (is_array($selectedLeft)) {
        $selectedLeft = $selectedLeft[0] ?? null;
    }
@endphp

<div class="hearing-ear-options row g-3">
    @foreach(['right' => $selectedRight, 'left' => $selectedLeft] as $ear => $selected)
        <div class="col-md-6">
            <div class="hearing-ear-options__panel border rounded p-3 h-100">
                <div class="small fw-semibold mb-2">
                    <i class="ti ti-ear me-1"></i>{{ __('workflow.fields.imaging_ear_'.$ear) }}
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($options as $code => $label)
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="radio"
                                   name="{{ $namePrefix }}[assessments][{{ $type }}][{{ $ear }}]"
                                   id="{{ $namePrefix }}_{{ $type }}_{{ $ear }}_{{ $code }}"
                                   value="{{ $code }}"
                                   @checked((string) $selected === (string) $code)>
                            <label class="form-check-label small"
                                   for="{{ $namePrefix }}_{{ $type }}_{{ $ear }}_{{ $code }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
