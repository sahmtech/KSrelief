@extends('layouts.admin')

@section('title', __('patients.title'))

@section('content')
<x-page-header
    :title="__('patients.title')"
    :subtitle="__('patients.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title')],
    ]"
>
    <x-slot:actions>
        @can('importExcel', \App\Models\Patient::class)
            <a href="{{ route('patients.import.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-file-spreadsheet me-1"></i> {{ __('patients.import.create_title') }}
            </a>
        @endcan
        @can('create', \App\Models\Patient::class)
            <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> {{ __('patients.add') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.total')" :value="$stats['total']" icon="ti ti-users" variant="primary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.accepted')" :value="$stats['accepted']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.rejected')" :value="$stats['rejected']" icon="ti ti-circle-x" variant="danger" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.postponed')" :value="$stats['postponed']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.cancelled')" :value="$stats['cancelled']" icon="ti ti-ban" variant="secondary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.admitted')" :value="$stats['admitted']" icon="ti ti-bed" variant="primary" />
    </div>
    <div class="col-6 col-md-4 col-xl">
        <x-stats-card :label="__('patients.stats.completed')" :value="$stats['completed']" icon="ti ti-check" variant="success" />
    </div>
</div>

<x-card :title="__('patients.filters.title')" :compact="true" class="mb-4">
    <form method="GET" action="{{ route('patients.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-3">
            <label class="form-group-admin__label">{{ __('patients.filters.search') }}</label>
            <input type="search" name="search" class="form-group-admin__input" value="{{ $filters['search'] }}" placeholder="{{ __('patients.filters.search_placeholder') }}">
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('patients.filters.campaign') }}</label>
            <select name="campaign_id" class="form-group-admin__input">
                <option value="">{{ __('patients.filters.all_campaigns') }}</option>
                @foreach($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected((string) $filters['campaign_id'] === (string) $campaign->id)>{{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('patients.filters.eligibility') }}</label>
            <select name="eligibility_status_id" class="form-group-admin__input">
                <option value="">{{ __('patients.filters.all_eligibility') }}</option>
                @foreach($eligibilityStatuses as $status)
                    <option value="{{ $status->id }}" @selected((string) $filters['eligibility_status_id'] === (string) $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('patients.filters.stage') }}</label>
            <select name="current_stage_id" class="form-group-admin__input">
                <option value="">{{ __('patients.filters.all_stages') }}</option>
                @foreach($patientStages as $stage)
                    <option value="{{ $stage->id }}" @selected((string) $filters['current_stage_id'] === (string) $stage->id)>{{ $stage->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('patients.filters.admission') }}</label>
            <select name="admission_status" class="form-group-admin__input">
                <option value="">{{ __('patients.filters.all_admission') }}</option>
                @foreach($admissionStatuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['admission_status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('patients.filters.gender') }}</label>
            <select name="gender" class="form-group-admin__input">
                <option value="">{{ __('patients.filters.all_genders') }}</option>
                @foreach($genders as $gender)
                    <option value="{{ $gender->value }}" @selected($filters['gender'] === $gender->value)>{{ $gender->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('patients.filters.created_from') }}</label>
            <input type="date" name="created_from" class="form-group-admin__input" value="{{ $filters['created_from'] }}">
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-group-admin__label">{{ __('patients.filters.created_to') }}</label>
            <input type="date" name="created_to" class="form-group-admin__input" value="{{ $filters['created_to'] }}">
        </div>
        <div class="col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('patients.filters.apply') }}</button>
            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('patients.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <x-datatable
        id="patientsTable"
        :options="[
            'order' => [[8, 'desc']],
            'columnDefs' => [
                ['targets' => 9, 'orderable' => false, 'width' => '60px', 'className' => 'text-end'],
            ],
        ]"
    >
        <x-slot:head>
            <tr>
                <th>{{ __('patients.table.file_number') }}</th>
                <th>{{ __('patients.table.name') }}</th>
                <th>{{ __('patients.table.campaign') }}</th>
                <th>{{ __('patients.table.age') }}</th>
                <th>{{ __('patients.table.gender') }}</th>
                <th>{{ __('patients.table.eligibility') }}</th>
                <th>{{ __('patients.table.stage') }}</th>
                <th>{{ __('patients.table.admission') }}</th>
                <th>{{ __('patients.table.created_at') }}</th>
                <th class="text-end">{{ __('patients.table.actions') }}</th>
            </tr>
        </x-slot:head>
        @forelse($patients as $patient)
            <tr>
                <td>
                    <x-record-code-link :href="route('patients.show', $patient)" :code="$patient->file_number" />
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <x-patient-avatar :patient="$patient" size="sm" />
                        <a href="{{ route('patients.show', $patient) }}" class="fw-medium text-decoration-none text-truncate">
                            {{ $patient->patient_name }}
                        </a>
                    </div>
                </td>
                <td>
                    <x-record-code-link :href="route('campaigns.show', $patient->campaign)" :code="$patient->campaign->code" />
                </td>
                <td>{{ $patient->ageLabel() }}</td>
                <td>{{ $patient->gender?->label() ?? '—' }}</td>
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
                <td>{{ $patient->created_at->format('Y-m-d') }}</td>
                <td class="text-end table-actions">
                    <div class="dropdown">
                        <button
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            data-table-dropdown
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-label="{{ __('patients.table.actions') }}"
                        >
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item" href="{{ route('patients.show', $patient) }}">
                                    <i class="ti ti-eye me-2"></i>{{ __('patients.actions.view') }}
                                </a>
                            </li>
                            @can('update', $patient)
                                <li>
                                    <a class="dropdown-item" href="{{ route('patients.edit', $patient) }}">
                                        <i class="ti ti-pencil me-2"></i>{{ __('patients.actions.edit') }}
                                    </a>
                                </li>
                            @endcan
                            @can('delete', $patient)
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('patients.destroy', $patient) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('patients.messages.confirm_delete') }}">
                                            <i class="ti ti-trash me-2"></i>{{ __('patients.actions.delete') }}
                                        </button>
                                    </form>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center text-muted py-4">{{ __('patients.messages.empty') }}</td>
            </tr>
        @endforelse
    </x-datatable>
</x-card>
@endsection
