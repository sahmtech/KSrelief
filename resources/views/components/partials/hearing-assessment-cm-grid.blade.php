@props([
    'namePrefix',
    'type',
    'assessment' => [],
])

<div class="hearing-cm-section mt-2">
    <div class="small fw-semibold text-muted mb-2">{{ __('workflow.hearing_assessment.sections.cm') }}</div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 hearing-cm-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 11rem;">{{ __('workflow.hearing_assessment.table.ear') }}</th>
                    <th class="text-center small">{{ __('workflow.hearing_assessment.sections.cm') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['right', 'left'] as $ear)
                    <tr>
                        <td class="fw-semibold small text-nowrap">
                            {{ __('workflow.fields.imaging_ear_'.$ear) }}
                        </td>
                        <td class="p-1">
                            <input type="text"
                                   class="form-control form-control-sm"
                                   name="{{ $namePrefix }}[assessments][{{ $type }}][cm][{{ $ear }}]"
                                   value="{{ $assessment[$ear] ?? '' }}"
                                   placeholder="—">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
