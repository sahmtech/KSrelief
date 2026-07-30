@if(config('app.debug') && $debugBackfill)
    <x-card :title="__('settings.debug.title')" class="mt-3 border border-warning-subtle">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="stats-card__icon stats-card__icon--warning flex-shrink-0">
                <i class="ti ti-tool"></i>
            </div>
            <div>
                <p class="text-muted mb-1" style="font-size: 0.875rem;">{{ __('settings.debug.subtitle') }}</p>
                <p class="mb-0" style="font-size: 0.8125rem;">
                    {{ __('settings.debug.format_hint', ['example' => app(\App\Support\RecordCodeGenerator::class)->examplePatientFileNumber()]) }}
                </p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <div class="text-muted small">{{ __('settings.debug.missing_campaign_codes') }}</div>
                    <div class="fs-5 fw-semibold">{{ number_format($debugBackfill['campaigns']) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <div class="text-muted small">{{ __('settings.debug.missing_patient_codes') }}</div>
                    <div class="fs-5 fw-semibold">{{ number_format($debugBackfill['patients_missing']) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <div class="text-muted small">{{ __('settings.debug.legacy_patient_codes') }}</div>
                    <div class="fs-5 fw-semibold text-warning">{{ number_format($debugBackfill['patients_legacy']) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border rounded p-3 h-100 bg-light-subtle">
                    <div class="text-muted small">{{ __('settings.debug.modern_patient_codes') }}</div>
                    <div class="fs-5 fw-semibold text-success">{{ number_format($debugBackfill['patients_modern']) }}</div>
                </div>
            </div>
        </div>

        @if(! empty($debugBackfill['legacy_samples']))
            <div class="mb-4">
                <h6 class="fw-semibold mb-2">{{ __('settings.debug.legacy_samples_title') }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('patients.table.name') }}</th>
                                <th>{{ __('patients.fields.file_number') }}</th>
                                <th>{{ __('menu.campaigns') }}</th>
                                <th>{{ __('patients.table.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debugBackfill['legacy_samples'] as $sample)
                                <tr>
                                    <td>{{ $sample['patient_name'] }}</td>
                                    <td><code>{{ $sample['file_number'] ?? '—' }}</code></td>
                                    <td>{{ $sample['campaign'] ?? '—' }}</td>
                                    <td class="text-muted">{{ $sample['created_at'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('settings.backfill-record-codes') }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-warning btn-sm"
                    @disabled($debugBackfill['campaigns'] === 0 && $debugBackfill['patients_missing'] === 0)
                    data-confirm="{{ __('settings.debug.backfill_confirm') }}"
                >
                    <i class="ti ti-barcode me-1"></i>{{ __('settings.debug.backfill_action') }}
                </button>
            </form>

            <form method="POST" action="{{ route('settings.migrate-patient-file-numbers') }}">
                @csrf
                <button
                    type="submit"
                    class="btn btn-outline-warning btn-sm"
                    @disabled($debugBackfill['patients_legacy'] === 0)
                    data-confirm="{{ __('settings.debug.migrate_confirm') }}"
                >
                    <i class="ti ti-refresh me-1"></i>{{ __('settings.debug.migrate_action') }}
                </button>
            </form>
        </div>
    </x-card>
@endif
