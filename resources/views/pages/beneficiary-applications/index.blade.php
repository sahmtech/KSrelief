@extends('layouts.admin')

@section('title', __('beneficiary_registration.staff_title'))

@section('content')
<x-page-header
    :title="__('beneficiary_registration.staff_title')"
    :subtitle="__('beneficiary_registration.staff_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('beneficiary_registration.menu')],
    ]"
/>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('beneficiary_registration.status.pending')" :value="$stats['pending']" icon="ti ti-clock" variant="warning" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('beneficiary_registration.status.accepted')" :value="$stats['accepted']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('beneficiary_registration.status.rejected')" :value="$stats['rejected']" icon="ti ti-circle-x" variant="danger" />
    </div>
    <div class="col-6 col-md-3">
        <x-stats-card :label="__('patients.stats.total')" :value="$stats['total']" icon="ti ti-list" variant="primary" />
    </div>
</div>

<x-card :title="__('patients.filters.title')" :compact="true" class="mb-4">
    <form method="GET" action="{{ route('beneficiary-applications.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label class="form-group-admin__label">{{ __('beneficiary_registration.filters.search') }}</label>
            <input type="search" name="search" class="form-group-admin__input" value="{{ $filters['search'] }}">
        </div>
        <div class="col-md-4 col-lg-3">
            <label class="form-group-admin__label">{{ __('beneficiary_registration.filters.status') }}</label>
            <select name="status" class="form-group-admin__input">
                <option value="">{{ __('beneficiary_registration.filters.all') }}</option>
                @foreach($meta['statuses'] ?? [] as $status)
                    <option value="{{ $status['code'] }}" @selected($filters['status'] === $status['code'])>{{ $status['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-3">
            <label class="form-group-admin__label">{{ __('beneficiary_registration.filters.extra_type') }}</label>
            <select name="extra_type" class="form-group-admin__input">
                <option value="">{{ __('beneficiary_registration.filters.all') }}</option>
                @foreach($meta['extra_types'] ?? [] as $type)
                    <option value="{{ $type['id'] }}" @selected((string) $filters['extra_type'] === (string) $type['id'])>{{ $type['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <button type="submit" class="btn btn-primary w-100">{{ __('patients.filters.apply') }}</button>
        </div>
    </form>
</x-card>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('beneficiary_registration.fields.name') }}</th>
                    <th>{{ __('beneficiary_registration.fields.national_number') }}</th>
                    <th>{{ __('beneficiary_registration.fields.mobile') }}</th>
                    <th>{{ __('beneficiary_registration.fields.extra_type') }}</th>
                    <th>{{ __('beneficiary_registration.filters.status') }}</th>
                    <th>{{ __('common.created_at') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td class="fw-semibold">{{ $application->full_name }}</td>
                        <td>{{ $application->national_number }}</td>
                        <td>{{ $application->mobile }}</td>
                        <td>{{ $application->extraTypeLabel() }}</td>
                        <td>
                            <span class="badge text-bg-{{ $application->status->badgeClass() }}">
                                {{ $application->status->label() }}
                            </span>
                        </td>
                        <td>{{ $application->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('beneficiary-applications.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('beneficiary_registration.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('beneficiary_registration.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($applications->hasPages())
        <div class="card-footer bg-white">{{ $applications->links() }}</div>
    @endif
</div>
@endsection
