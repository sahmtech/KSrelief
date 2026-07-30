@extends('layouts.admin')

@section('title', __('patients.brief.title').' — '.$patient->patient_name)

@section('content')
@include('pages.patients.partials.clinical-fallback-styles')

@php
    $stageSummaries = $brief['stage_summaries'] ?? [];
    $priorityClinical = $brief['priority_clinical'] ?? [];
    $surgeryContext = $brief['surgery_context'] ?? [];
    $demographics = $brief['demographics'] ?? [];
    $recordOverview = $brief['record_overview'] ?? ['total' => 0, 'has_history' => false];
    $phases = $brief['phases'] ?? [];
    $totalPhaseItems = collect($phases)->sum(fn ($phase) => count($phase['items'] ?? []));
@endphp

<div class="patient-brief">
    <x-page-header
        :title="__('patients.brief.title')"
        :subtitle="__('patients.brief.subtitle')"
        :breadcrumbs="[
            ['label' => __('menu.patients')],
            ['label' => __('patients.title'), 'url' => route('patients.index')],
            ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
            ['label' => __('patients.brief.title')],
        ]"
    >
        <x-slot:actions>
            <x-export-operative-note-button :patient="$patient" size="sm" />
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-id me-1"></i>{{ __('patients.brief.full_profile') }}
            </a>
            @can('update', $patient)
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-pencil me-1"></i>{{ __('patients.actions.edit') }}
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Clinical identity banner --}}
    <section class="patient-brief-banner mb-3">
        <div class="patient-brief-banner__identity">
            <x-patient-avatar :patient="$patient" size="lg" />
            <div class="min-w-0">
                <div class="patient-brief-banner__eyebrow">{{ __('patients.brief.preop_review') }}</div>
                <h1 class="patient-brief-banner__name">{{ $patient->patient_name }}</h1>
                <div class="patient-brief-banner__meta">
                    @if($patient->file_number)
                        <x-patient-file-number :patient="$patient" code-class="patient-brief-banner__code" />
                    @endif
                    <span>{{ $patient->ageLabel() }}</span>
                    @if($patient->gender)
                        <span>{{ $patient->gender->label() }}</span>
                    @endif
                    @if(filled($patient->height_cm) || filled($patient->weight_kg))
                        <span>
                            @if(filled($patient->height_cm)){{ $patient->heightLabel() }}@endif
                            @if(filled($patient->height_cm) && filled($patient->weight_kg)) · @endif
                            @if(filled($patient->weight_kg)){{ $patient->weightLabel() }}@endif
                        </span>
                    @endif
                </div>
                <div class="patient-brief-banner__badges">
                    @if($patient->eligibilityStatus)
                        <span class="patient-brief-pill" style="--pill-color: {{ $patient->eligibilityStatus->color }};">
                            {{ $patient->eligibilityStatus->name }}
                        </span>
                    @endif
                    @if($patient->currentStage)
                        <span class="patient-brief-pill patient-brief-pill--neutral">
                            <i class="ti ti-route me-1"></i>{{ $patient->currentStage->displayName() }}
                        </span>
                    @endif
                    <span class="badge-status {{ $patient->admissionBadgeClass() }}">{{ $patient->admissionLabel() }}</span>
                </div>
            </div>
        </div>

        <div class="patient-brief-banner__aside">
            @if($patient->campaign)
                <div class="patient-brief-banner__campaign">
                    <span class="patient-brief-banner__aside-label">{{ __('patients.fields.campaign') }}</span>
                    <a href="{{ route('campaigns.show', $patient->campaign) }}" class="patient-brief-banner__campaign-link">
                        {{ $patient->campaign->name }}
                    </a>
                    @if($patient->campaign->code)
                        <code class="patient-brief-banner__campaign-code">{{ $patient->campaign->code }}</code>
                    @endif
                </div>
            @endif
            @can('viewAny', [\App\Models\MedicalRecord::class, $patient])
                @if(($recordOverview['total'] ?? 0) > 0)
                    <div class="patient-brief-banner__records">
                        <span class="patient-brief-banner__aside-label">{{ __('patients.brief.records_label') }}</span>
                        <strong>{{ $recordOverview['total'] }}</strong>
                        <a href="{{ route('patients.show', $patient) }}#records-dossier" class="small">
                            {{ __('patients.brief.view_all_records') }}
                        </a>
                    </div>
                @endif
            @endcan
        </div>
    </section>

    @if(filled($patient->notes) || filled($patient->approval_reason))
        <div class="patient-brief-alert mb-3" role="status">
            <div class="patient-brief-alert__icon"><i class="ti ti-alert-triangle"></i></div>
            <div class="patient-brief-alert__body">
                <div class="patient-brief-alert__title">{{ __('patients.brief.clinical_alerts') }}</div>
                @if(filled($patient->approval_reason))
                    <div><span class="fw-semibold">{{ __('patients.fields.approval_reason') }}:</span> {{ $patient->approval_reason }}</div>
                @endif
                @if(filled($patient->notes))
                    <div><span class="fw-semibold">{{ __('patients.fields.notes') }}:</span> {{ $patient->notes }}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- At a glance --}}
    @if(!empty($surgeryContext))
        <section class="patient-brief-section mb-4">
            <div class="patient-brief-section__head">
                <h2 class="patient-brief-section__title">{{ __('patients.brief.at_a_glance') }}</h2>
                <p class="patient-brief-section__hint">{{ __('patients.brief.at_a_glance_hint') }}</p>
            </div>
            <div class="patient-brief-vitals">
                @foreach($surgeryContext as $item)
                    <div @class(['patient-brief-vital', 'patient-brief-vital--accent' => !empty($item['highlight'])])>
                        <div class="patient-brief-vital__label">{{ $item['label'] }}</div>
                        <div class="patient-brief-vital__value">
                            <x-brief-clinical-value
                                :value="$item['value']"
                                :type="$item['type'] ?? null"
                                :field-definition="$item['field_definition'] ?? []"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Workflow progress --}}
    @if(!empty($stageSummaries))
        <nav class="patient-brief-rail mb-4" aria-label="{{ __('patients.brief.workflow_progress') }}">
            @foreach($stageSummaries as $index => $stage)
                <a href="#brief-stage-{{ $stage['code'] ?? $index }}" class="patient-brief-rail__item">
                    <span class="patient-brief-rail__index">{{ $index + 1 }}</span>
                    <span class="patient-brief-rail__name">{{ $stage['name'] }}</span>
                    @if(!empty($stage['record_date']))
                        <span class="patient-brief-rail__date">{{ $stage['record_date'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <section class="patient-brief-panel h-100">
                <div class="patient-brief-panel__head">
                    <h2 class="patient-brief-panel__title">{{ __('patients.brief.priority_clinical') }}</h2>
                    <span class="patient-brief-panel__meta">{{ __('patients.brief.priority_clinical_hint') }}</span>
                </div>
                @if(!empty($priorityClinical))
                    <div class="patient-brief-priority-grid">
                        @foreach($priorityClinical as $item)
                            <article class="patient-brief-priority-item">
                                <h3 class="patient-brief-priority-item__label">{{ $item['label'] }}</h3>
                                <div class="patient-brief-priority-item__value">
                                    @if(!empty($item['color']))
                                        <span class="fw-semibold" style="color: {{ $item['color'] }};">{{ $item['value'] }}</span>
                                    @else
                                        <x-brief-clinical-value
                                            :value="$item['value']"
                                            :type="$item['type'] ?? null"
                                            :field-definition="$item['field_definition'] ?? []"
                                            :link-label="$item['label']"
                                        />
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 small px-3 pb-3">{{ __('patients.clinical.no_phase_data') }}</p>
                @endif
            </section>
        </div>

        <div class="col-xl-4">
            <section class="patient-brief-panel h-100">
                <div class="patient-brief-panel__head">
                    <h2 class="patient-brief-panel__title">{{ __('patients.brief.demographics') }}</h2>
                </div>
                <dl class="patient-brief-dl">
                    @foreach($demographics as $item)
                        <div class="patient-brief-dl__row">
                            <dt>{{ $item['label'] }}</dt>
                            <dd>{{ $item['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>
    </div>

    @include('pages.patients.partials.brief-attachments', ['patient' => $patient])

    @if(!empty($stageSummaries))
        <section class="patient-brief-section mb-4">
            <div class="patient-brief-section__head">
                <h2 class="patient-brief-section__title">{{ __('patients.brief.stage_records') }}</h2>
                <p class="patient-brief-section__hint">{{ __('patients.brief.stage_records_hint') }}</p>
            </div>

            <div class="patient-brief-stages">
                @foreach($stageSummaries as $index => $stage)
                    @php
                        $visibleItems = 6;
                        $stageItems = $stage['items'];
                        $hasMoreItems = count($stageItems) > $visibleItems;
                        $stageCode = $stage['code'] ?? $index;
                    @endphp
                    <article class="patient-brief-stage" id="brief-stage-{{ $stageCode }}">
                        <header class="patient-brief-stage__head">
                            <div>
                                <div class="patient-brief-stage__kicker">{{ __('patients.brief.stage_n', ['n' => $index + 1]) }}</div>
                                <h3 class="patient-brief-stage__title">{{ $stage['name'] }}</h3>
                            </div>
                            <div class="patient-brief-stage__actions">
                                @if(!empty($stage['record_date']))
                                    <span class="patient-brief-stage__date">{{ $stage['record_date'] }}</span>
                                @endif
                                @if(($stage['record_count'] ?? 1) > 1)
                                    <span class="patient-brief-pill patient-brief-pill--neutral">
                                        {{ __('patients.brief.stage_record_count', ['count' => $stage['record_count']]) }}
                                    </span>
                                @endif
                                @can('viewAny', [\App\Models\MedicalRecord::class, $patient])
                                    @if(!empty($stage['record_id']))
                                        <a href="{{ route('patients.records.show', [$patient, $stage['record_id']]) }}" class="btn btn-sm btn-outline-primary">
                                            {{ __('patients.brief.view_record') }}
                                        </a>
                                        @if(($stage['code'] ?? '') === 'operation')
                                            <a href="{{ route('patients.records.export-operation-pdf', [$patient, $stage['record_id']]) }}" class="btn btn-sm btn-outline-success">
                                                {{ __('workflow.operation.export_pdf') }}
                                            </a>
                                        @endif
                                    @endif
                                @endcan
                            </div>
                        </header>

                        <div class="patient-brief-stage__body">
                            <ul class="patient-brief-stage__list" data-brief-stage-list>
                                @foreach($stageItems as $itemIndex => $item)
                                    <li @class(['d-none' => $itemIndex >= $visibleItems, 'brief-stage-extra' => $itemIndex >= $visibleItems])>
                                        <span class="patient-brief-stage__field">{{ $item['label'] }}</span>
                                        <div class="patient-brief-stage__value">
                                            @if(!empty($item['color']))
                                                <span class="fw-semibold" style="color: {{ $item['color'] }};">{{ $item['value'] }}</span>
                                            @else
                                                <x-brief-clinical-value
                                                    :value="$item['value']"
                                                    :type="$item['type'] ?? null"
                                                    :field-definition="$item['field_definition'] ?? []"
                                                    :link-label="$item['label']"
                                                />
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            @if($hasMoreItems)
                                <button type="button"
                                        class="patient-brief-stage__toggle"
                                        data-brief-stage-toggle
                                        data-show-more="{{ __('patients.brief.show_more_fields', ['count' => count($stageItems) - $visibleItems]) }}"
                                        data-show-less="{{ __('patients.brief.show_less_fields') }}">
                                    {{ __('patients.brief.show_more_fields', ['count' => count($stageItems) - $visibleItems]) }}
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($clinicalProfile && !empty($phases))
        <section class="patient-brief-panel mb-4">
            <div class="patient-brief-panel__head patient-brief-panel__head--split">
                <div>
                    <h2 class="patient-brief-panel__title">{{ __('patients.brief.clinical_phases') }}</h2>
                    <span class="patient-brief-panel__meta">{{ __('patients.brief.clinical_phases_hint') }}</span>
                </div>
                @if($totalPhaseItems > 8)
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            data-bs-toggle="collapse"
                            data-bs-target="#briefClinicalPhases"
                            aria-expanded="false">
                        {{ __('patients.brief.expand_phases') }}
                    </button>
                @endif
            </div>

            <div @class(['collapse' => $totalPhaseItems > 8, 'show' => $totalPhaseItems <= 8]) id="briefClinicalPhases">
                <div class="patient-brief-phases">
                    @foreach($phases as $phaseCode => $phase)
                        <div class="patient-brief-phase" style="--phase-color: {{ $phase['color'] }}; --phase-bg: {{ $phase['background'] }};">
                            <div class="patient-brief-phase__head">
                                <h3 class="patient-brief-phase__title">{{ $phase['label'] }}</h3>
                                <span class="patient-brief-pill patient-brief-pill--neutral">{{ count($phase['items']) }}</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm patient-brief-phase__table mb-0">
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
                                                <td class="fw-semibold">{{ $item['label'] }}</td>
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
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <footer class="patient-brief-footer">
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-id me-1"></i>{{ __('patients.brief.full_profile') }}
        </a>
        @can('viewAny', [\App\Models\MedicalRecord::class, $patient])
            <a href="{{ route('patients.show', $patient) }}#records-dossier" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-file-medical me-1"></i>{{ __('patients.tabs.records') }}
            </a>
        @endcan
        @can('create', [\App\Models\MedicalRecord::class, $patient])
            <a href="{{ route('patients.records.create', $patient) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i>{{ __('workflow.records.add') }}
            </a>
        @endcan
    </footer>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-brief-stage-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const card = button.closest('.patient-brief-stage');
        const extras = card?.querySelectorAll('.brief-stage-extra');
        if (!extras?.length) return;

        const expanded = button.dataset.expanded === '1';
        extras.forEach((item) => item.classList.toggle('d-none', expanded));
        button.dataset.expanded = expanded ? '0' : '1';
        button.textContent = expanded ? button.dataset.showMore : button.dataset.showLess;
    });
});
</script>
@endpush
@endsection
