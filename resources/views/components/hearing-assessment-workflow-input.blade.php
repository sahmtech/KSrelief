@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    use App\Support\HearingAssessmentSupport;

    $raw = is_array(old($namePrefix)) ? old($namePrefix) : (is_array($savedValue) ? $savedValue : []);
    $data = HearingAssessmentSupport::normalize($raw);
    $selectedTypes = $data['hearing_types'];
@endphp

<div class="hearing-assessment-workflow" data-hearing-assessment-root>
    <div class="hearing-assessment-workflow__intro mb-3">
        <label class="form-label small fw-semibold mb-1" for="{{ $namePrefix }}_hearing_types">
            {{ __('workflow.hearing_assessment.type_heading') }}
        </label>
        <div class="text-muted small mb-2">{{ __('workflow.hearing_assessment.type_hint') }}</div>
        <div class="hearing-type-select"
             data-hearing-type-select
             data-placeholder="{{ __('workflow.hearing_assessment.type_placeholder') }}">
            <select id="{{ $namePrefix }}_hearing_types"
                    class="form-select hearing-type-select__control"
                    name="{{ $namePrefix }}[hearing_types][]"
                    multiple
                    data-hearing-type-select2>
                @foreach(HearingAssessmentSupport::types() as $typeKey => $meta)
                    <option value="{{ $typeKey }}" @selected(in_array($typeKey, $selectedTypes, true))>
                        {{ __($meta['label_key']) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="hearing-type-panels d-flex flex-column gap-3">
        @foreach(HearingAssessmentSupport::types() as $typeKey => $meta)
            @php
                $layout = (string) ($meta['layout'] ?? '');
                $assessment = $data['assessments'][$typeKey] ?? [];
                $isActive = in_array($typeKey, $selectedTypes, true);
            @endphp
            <div class="hearing-type-panel card border-0 shadow-sm{{ $isActive ? '' : ' d-none' }}"
                 data-hearing-type-panel="{{ $typeKey }}">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="fw-semibold small mb-0">{{ __($meta['label_key']) }}</div>
                    <span class="badge text-bg-light border">{{ __('workflow.hearing_assessment.panel_badge') }}</span>
                </div>
                <div class="card-body p-3">
                    @if($layout === 'ear_options')
                        @include('components.partials.hearing-assessment-ear-options', [
                            'type' => $typeKey,
                            'namePrefix' => $namePrefix,
                            'assessment' => $assessment,
                        ])
                    @elseif($layout === 'bc_ac_table')
                        @include('components.partials.hearing-assessment-frequency-grid', [
                            'namePrefix' => $namePrefix,
                            'type' => $typeKey,
                            'sectionKey' => 'bc',
                            'sectionLabel' => __('workflow.hearing_assessment.sections.bc'),
                            'grid' => $assessment['bc'] ?? [],
                        ])
                        @include('components.partials.hearing-assessment-frequency-grid', [
                            'namePrefix' => $namePrefix,
                            'type' => $typeKey,
                            'sectionKey' => 'ac',
                            'sectionLabel' => __('workflow.hearing_assessment.sections.ac'),
                            'grid' => $assessment['ac'] ?? [],
                        ])
                        @if(in_array('cm', $meta['extras'] ?? [], true))
                            @include('components.partials.hearing-assessment-cm-grid', [
                                'namePrefix' => $namePrefix,
                                'type' => $typeKey,
                                'assessment' => $assessment['cm'] ?? [],
                            ])
                        @endif
                    @elseif($layout === 'ff_table')
                        @include('components.partials.hearing-assessment-frequency-grid', [
                            'namePrefix' => $namePrefix,
                            'type' => $typeKey,
                            'sectionKey' => 'ff',
                            'sectionLabel' => __('workflow.hearing_assessment.sections.ff'),
                            'grid' => $assessment['ff'] ?? [],
                        ])
                    @elseif($layout === 'speech_table')
                        @include('components.partials.hearing-assessment-speech-grid', [
                            'namePrefix' => $namePrefix,
                            'type' => $typeKey,
                            'assessment' => $assessment,
                        ])
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
