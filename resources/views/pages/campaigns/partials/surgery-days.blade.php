<x-card :title="__('campaigns.surgery_days.title')" class="mb-4">
    @if(empty($surgeryDaysSchedule))
        <div class="text-center text-muted py-4">{{ __('campaigns.surgery_days.empty') }}</div>
    @else
        <div class="accordion campaign-day-accordion" id="campaignSurgeryDays">
            @foreach($surgeryDaysSchedule as $index => $day)
                @php
                    $collapseId = 'surgery-day-'.$day['day_number'];
                    $isOpen = $index === 0;
                @endphp
                <div class="accordion-item campaign-day-accordion__item">
                    <h2 class="accordion-header" id="heading-{{ $collapseId }}">
                        <button class="accordion-button @unless($isOpen) collapsed @endunless" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
                            <span class="campaign-day-accordion__heading">
                                <span class="campaign-day-accordion__day">{{ __('campaigns.surgery_days.day', ['number' => $day['day_number']]) }}</span>
                            </span>
                            <span class="campaign-day-accordion__badges">
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="ti ti-user-heart me-1"></i>{{ $day['count'] }}
                                </span>
                            </span>
                        </button>
                    </h2>
                    <div id="{{ $collapseId }}" class="accordion-collapse collapse @if($isOpen) show @endif" data-bs-parent="#campaignSurgeryDays">
                        <div class="accordion-body p-0">
                            @if($day['patients']->isEmpty())
                                <div class="text-muted p-4">{{ __('campaigns.surgery_days.no_patients') }}</div>
                            @else
                                <div class="table-responsive admin-table-scroll">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('patients.fields.rank') }}</th>
                                                <th>{{ __('patients.table.name') }}</th>
                                                <th>{{ __('patients.table.file_number') }}</th>
                                                <th>{{ __('patients.table.age') }}</th>
                                                <th>{{ __('patients.table.eligibility') }}</th>
                                                <th>{{ __('patients.fields.surgical_side') }}</th>
                                                <th>{{ __('patients.table.stage') }}</th>
                                                <th class="text-end">{{ __('patients.table.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($day['patients'] as $patient)
                                                <tr>
                                                    <td>{{ $patient->rank ?? '—' }}</td>
                                                    <td class="fw-medium">{{ $patient->patient_name }}</td>
                                                    <td><code>{{ $patient->file_number ?? '—' }}</code></td>
                                                    <td>{{ $patient->ageLabel() }}</td>
                                                    <td>{{ $patient->eligibilityStatus?->name ?? '—' }}</td>
                                                    <td>{{ $patient->surgicalSideLabel() }}</td>
                                                    <td>{{ $patient->currentStage?->name ?? '—' }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary" title="{{ __('patients.actions.view') }}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-card>
