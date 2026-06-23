@php
    $phases = $clinicalProfile['phases'] ?? [];
    $phaseOrder = ['screening', 'pre_op', 'intra_op', 'post_op', 'follow_up'];
@endphp

@include('pages.patients.partials.clinical-fallback-styles')

<div class="clinical-dossier">
    <p class="text-muted mb-2" style="font-size: 0.875rem;">{{ __('workflow.records.dossier_subtitle') }}</p>
    <div class="alert alert-light border py-2 px-3 mb-4" style="font-size: 0.8125rem;">
        <i class="ti ti-brand-google-drive me-1 text-primary"></i>
        {{ __('workflow.records.drive_links_hint') }}
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach($phaseOrder as $phaseCode)
            @if(isset($phases[$phaseCode]))
                <span class="badge clinical-phase-badge" style="background: {{ $phases[$phaseCode]['background'] }}; color: #1f2937;">
                    {{ $phases[$phaseCode]['label'] }}
                </span>
            @endif
        @endforeach
    </div>

    @foreach($phaseOrder as $phaseCode)
        @php $phase = $phases[$phaseCode] ?? null; @endphp
        @if($phase)
            <div class="clinical-phase-panel mb-4" style="--clinical-phase-bg: {{ $phase['background'] }}; --clinical-phase-color: {{ $phase['color'] }};">
                <div class="clinical-phase-panel__header">
                    <h6 class="mb-0">{{ $phase['label'] }}</h6>
                    <span class="badge bg-light text-dark border">{{ count($phase['items']) }}</span>
                </div>
                <div class="clinical-phase-panel__body">
                    @if(empty($phase['items']))
                        <p class="text-muted mb-0 small">{{ __('patients.clinical.no_phase_data') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('patients.clinical.field') }}</th>
                                        <th>{{ __('patients.clinical.value') }}</th>
                                        <th>{{ __('patients.clinical.source') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($phase['items'] as $item)
                                        <tr>
                                            <td class="fw-medium">{{ $item['label'] }}</td>
                                            <td>
                                        <x-clinical-value
                                            :value="$item['value']"
                                            :type="$item['type'] ?? null"
                                            :field-definition="$item['field_definition'] ?? []"
                                            :link-label="$item['label']"
                                        />
                                            </td>
                                            <td class="text-muted small">{{ $item['source'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endforeach

    @can('create', [\App\Models\MedicalRecord::class, $patient])
        <div class="d-flex flex-wrap gap-2 border-top pt-3">
            <a href="{{ route('patients.records.create', $patient) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i>{{ __('workflow.records.add') }}
            </a>
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-pencil me-1"></i>{{ __('patients.clinical.edit_screening') }}
            </a>
        </div>
    @endcan
</div>
