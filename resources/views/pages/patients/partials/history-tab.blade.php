{{-- Stage History tab on patient profile --}}
{{-- Requires: $patient, $stageHistory (Collection) --}}

<div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="mb-0 fw-semibold">
        <i class="ti ti-history me-2 text-primary"></i>
        {{ __('workflow.stage_history') }}
    </h6>
    @can('viewStageHistory', $patient)
    <a href="{{ route('patients.workflow.history', $patient) }}" class="btn btn-sm btn-outline-secondary">
        <i class="ti ti-external-link me-1"></i> {{ __('common.view_all') }}
    </a>
    @endcan
</div>

@if($stageHistory->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="ti ti-history d-block mb-2" style="font-size: 2.5rem; opacity: .4;"></i>
        {{ __('workflow.no_history') }}
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ __('workflow.history.date') }}</th>
                    <th>{{ __('workflow.history.from_stage') }}</th>
                    <th>{{ __('workflow.history.to_stage') }}</th>
                    <th>{{ __('workflow.history.changed_by') }}</th>
                    <th>{{ __('workflow.history.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stageHistory->take(10) as $entry)
                <tr>
                    <td class="text-nowrap small">{{ $entry->changed_at?->format('d M Y, H:i') }}</td>
                    <td>
                        @if($entry->fromStage)
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background-color: {{ $entry->fromStage->color ?? '#6B7280' }}; color:#fff;">
                                {{ $entry->fromStage->name }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge rounded-pill px-2 py-1"
                              style="background-color: {{ $entry->toStage->color ?? '#3B82F6' }}; color:#fff;">
                            {{ $entry->toStage->name }}
                        </span>
                    </td>
                    <td class="small">{{ $entry->changedBy?->name ?? '—' }}</td>
                    <td class="small text-muted">{{ $entry->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($stageHistory->count() > 10)
        <div class="text-center mt-2">
            <a href="{{ route('patients.workflow.history', $patient) }}" class="btn btn-sm btn-light">
                {{ __('common.view_all') }} ({{ $stageHistory->count() }})
            </a>
        </div>
    @endif
@endif
