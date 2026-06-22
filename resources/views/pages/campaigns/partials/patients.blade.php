{{-- Eligibility Stats --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.total')" :value="$patientStats['total']" icon="ti ti-users" variant="primary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.accepted')" :value="$patientStats['accepted']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.rejected')" :value="$patientStats['rejected']" icon="ti ti-circle-x" variant="danger" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.postponed')" :value="$patientStats['postponed']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.cancelled')" :value="$patientStats['cancelled']" icon="ti ti-ban" variant="secondary" />
    </div>
</div>

{{-- Stage Progress Stats --}}
@if($stageStats->isNotEmpty())
<div class="row g-3 mb-4">
    @foreach($stageStats as $stat)
    <div class="col-6 col-md-4 col-lg-3">
        <x-stats-card
            :label="$stat['stage']->name"
            :value="$stat['count']"
            icon="ti ti-stairs"
            :variant="match($stat['stage']->code) {
                'completed' => 'success',
                'operation' => 'danger',
                'admission' => 'primary',
                default => 'secondary',
            }"
        />
    </div>
    @endforeach
</div>
@endif

<div class="row g-3">
    <div class="col-12">
        <x-card :title="__('patients.campaign.title')" :flush="true">
            <x-slot:actions>
                @can('importExcel', \App\Models\Patient::class)
                    <a href="{{ route('patients.import.create', ['campaign_id' => $campaign->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-file-spreadsheet me-1"></i> {{ __('patients.import.create_title') }}
                    </a>
                @endcan
                @can('create', \App\Models\Patient::class)
                    <a href="{{ route('patients.create', ['campaign_id' => $campaign->id]) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i> {{ __('patients.campaign.add_patient') }}
                    </a>
                @endcan
            </x-slot:actions>

            @if($campaign->patients->isEmpty())
                <div class="text-center text-muted py-4">{{ __('patients.campaign.empty') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('patients.table.file_number') }}</th>
                                <th>{{ __('patients.table.name') }}</th>
                                <th>{{ __('patients.table.eligibility') }}</th>
                                <th>{{ __('patients.table.stage') }}</th>
                                <th>{{ __('patients.table.admission') }}</th>
                                <th class="text-end">{{ __('patients.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaign->patients as $patient)
                                <tr>
                                    <td>
                                        <x-record-code-link :href="route('patients.show', $patient)" :code="$patient->file_number" />
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <x-patient-avatar :patient="$patient" size="sm" />
                                            <div class="min-w-0">
                                                <a href="{{ route('patients.show', $patient) }}" class="fw-medium text-decoration-none">
                                                    {{ $patient->patient_name }}
                                                </a>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $patient->ageLabel() }} · {{ $patient->gender?->label() }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($patient->eligibilityStatus)
                                            <span class="badge border" style="background-color: {{ $patient->eligibilityStatus->color }}20; color: {{ $patient->eligibilityStatus->color }};">
                                                {{ $patient->eligibilityStatus->name }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $patient->currentStage?->name ?? '—' }}</td>
                                    <td><span class="badge-status {{ $patient->admissionBadgeClass() }}">{{ $patient->admissionLabel() }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</div>
