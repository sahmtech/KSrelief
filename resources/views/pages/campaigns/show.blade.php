@extends('layouts.admin')

@section('title', $campaign->name)

@section('content')
<x-page-header
    :title="__('campaigns.show_title')"
    :subtitle="__('campaigns.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.campaign_management')],
        ['label' => __('campaigns.title'), 'url' => route('campaigns.index')],
        ['label' => $campaign->name],
    ]"
/>

<div class="campaign-profile-hero">
    <div class="campaign-profile-hero__banner"></div>
    <div class="campaign-profile-hero__body">
        <div class="campaign-profile-hero__header">
            <div class="campaign-profile-hero__identity">
                <div class="campaign-profile-hero__icon">
                    <i class="ti ti-flag"></i>
                </div>
                <div class="campaign-profile-hero__info">
                    <h1 class="campaign-profile-hero__name">{{ $campaign->name }}</h1>
                    <p class="campaign-profile-hero__meta-text">{{ $campaign->country?->localizedName() }} · {{ $campaign->city?->localizedName() }} · {{ $campaign->specialty?->name }}</p>
                    <div class="campaign-profile-hero__badges">
                        <span class="badge-status {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span>
                        <span class="badge bg-light text-dark border">
                            <i class="ti ti-calendar me-1"></i>{{ $campaign->start_date->format('Y-m-d') }} — {{ $campaign->end_date->format('Y-m-d') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="campaign-profile-hero__actions">
                @can('viewCampaignDashboard', $campaign)
                <a href="{{ route('campaigns.dashboard', $campaign) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-chart-dots me-1"></i> {{ __('dashboard.campaign_title') }}
                </a>
                @endcan
                @can('update', $campaign)
                    <a href="{{ route('campaigns.edit', $campaign) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-pencil me-1"></i> {{ __('campaigns.actions.edit') }}
                    </a>
                @endcan
                @can('changeStatus', $campaign)
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changeCampaignStatusModal">
                        <i class="ti ti-switch-horizontal me-1"></i> {{ __('campaigns.actions.change_status') }}
                    </button>
                @endcan
                @can('delete', $campaign)
                    <form method="POST" action="{{ route('campaigns.destroy', $campaign) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('campaigns.messages.confirm_delete') }}">
                            <i class="ti ti-trash me-1"></i> {{ __('campaigns.actions.delete') }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <x-stats-card :label="__('campaigns.future_stats.patients')" :value="$futureStats['patients_count']" icon="ti ti-users" variant="primary" />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-stats-card :label="__('campaigns.future_stats.members')" :value="$futureStats['members_count']" icon="ti ti-stethoscope" variant="secondary" />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-stats-card :label="__('campaigns.future_stats.attendance')" :value="$futureStats['attendance_count']" icon="ti ti-clipboard-check" variant="success" />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-stats-card :label="__('campaigns.future_stats.trips')" :value="$futureStats['trips_count']" icon="ti ti-bus" variant="warning" />
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="campaignTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab">
            <i class="ti ti-info-circle me-1"></i> {{ __('campaigns.tabs.overview') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="patients-tab" data-bs-toggle="tab" data-bs-target="#patients-pane" type="button" role="tab">
            <i class="ti ti-user-heart me-1"></i> {{ __('campaigns.tabs.patients') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $futureStats['patients_count'] }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="team-tab" data-bs-toggle="tab" data-bs-target="#team-pane" type="button" role="tab">
            <i class="ti ti-users me-1"></i> {{ __('campaigns.tabs.team') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $futureStats['members_count'] }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button" role="tab">
            <i class="ti ti-clipboard-check me-1"></i> {{ __('campaigns.tabs.attendance') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $futureStats['attendance_count'] }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transportation-tab" data-bs-toggle="tab" data-bs-target="#transportation-pane" type="button" role="tab">
            <i class="ti ti-bus me-1"></i> {{ __('campaigns.tabs.transportation') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $futureStats['trips_count'] }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane" type="button" role="tab">
            <i class="ti ti-activity me-1"></i> {{ __('campaigns.tabs.activities') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $futureStats['activities_count'] }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="campaignTabsContent">
    <div class="tab-pane fade show active" id="overview-pane" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <x-card :title="__('campaigns.sections.overview')">
                    <div class="user-info-list">
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.objective') }}</div>
                            <div class="user-info-list__value">{{ $campaign->objective }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.target_group') }}</div>
                            <div class="user-info-list__value">{{ $campaign->target_group }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.shifts_count') }}</div>
                            <div class="user-info-list__value">{{ $campaign->shifts_count }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.expected_patients') }}</div>
                            <div class="user-info-list__value">{{ number_format($campaign->expected_patients) }}</div>
                        </div>
                        @if($campaign->description)
                            <div class="user-info-list__item">
                                <div class="user-info-list__label">{{ __('campaigns.fields.description') }}</div>
                                <div class="user-info-list__value">{{ $campaign->description }}</div>
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
            <div class="col-lg-4">
                <x-card :title="__('campaigns.fields.created_at')">
                    <div class="user-info-list">
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.created_by') }}</div>
                            <div class="user-info-list__value">{{ $campaign->creator?->name ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.created_at') }}</div>
                            <div class="user-info-list__value">{{ $campaign->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.updated_by') }}</div>
                            <div class="user-info-list__value">{{ $campaign->updater?->name ?? '—' }}</div>
                        </div>
                        <div class="user-info-list__item">
                            <div class="user-info-list__label">{{ __('campaigns.fields.updated_at') }}</div>
                            <div class="user-info-list__value">{{ $campaign->updated_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </x-card>

                <x-card :title="__('campaigns.wizard.title')" class="mt-3">
                    <p class="text-muted mb-3" style="font-size: 0.875rem;">{{ __('campaigns.wizard.subtitle') }}</p>
                    <ol class="campaign-wizard-steps mb-0">
                        @foreach(app(\App\Services\CampaignWizardService::class)->steps() as $step)
                            <li>{{ $step['label'] }}</li>
                        @endforeach
                    </ol>
                </x-card>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="patients-pane" role="tabpanel">
        @include('pages.campaigns.partials.patients', [
            'campaign' => $campaign,
            'patientStats' => $patientStats,
        ])
    </div>

    <div class="tab-pane fade" id="team-pane" role="tabpanel">
        @include('pages.campaigns.partials.team', [
            'campaign' => $campaign,
            'availableMembers' => $availableMembers,
        ])
    </div>

    <div class="tab-pane fade" id="attendance-pane" role="tabpanel">
        @include('pages.campaigns.partials.attendance', [
            'campaign' => $campaign,
            'attendanceStats' => $attendanceStats,
            'recentAttendances' => $recentAttendances,
        ])
    </div>

    <div class="tab-pane fade" id="transportation-pane" role="tabpanel">
        @include('pages.campaigns.partials.transportation', [
            'campaign' => $campaign,
            'transportStats' => $transportStats,
            'recentTrips' => $recentTrips,
        ])
    </div>

    <div class="tab-pane fade" id="activities-pane" role="tabpanel">
        @include('pages.campaigns.partials.activities', [
            'campaign' => $campaign,
            'activityStats' => $activityStats,
            'upcomingActivities' => $upcomingActivities,
            'recentActivities' => $recentActivities,
        ])
    </div>
</div>

@can('changeStatus', $campaign)
    <x-modal id="changeCampaignStatusModal" :title="__('campaigns.actions.change_status')">
        <form method="POST" action="{{ route('campaigns.status.update', $campaign) }}" id="changeCampaignStatusForm">
            @csrf
            @method('PATCH')
            <x-form-input :label="__('campaigns.fields.status')" name="campaign_status_id" type="select" required>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @selected((string) $campaign->campaign_status_id === (string) $status->id)>{{ $status->label() }}</option>
                @endforeach
            </x-form-input>
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
            <button type="submit" form="changeCampaignStatusForm" class="btn btn-primary">{{ __('common.save_changes') }}</button>
        </x-slot:footer>
    </x-modal>
@endcan
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    const tabMap = { '#team': 'team-tab', '#patients': 'patients-tab', '#attendance': 'attendance-tab', '#transportation': 'transportation-tab' };
    const tabId = tabMap[hash];

    if (tabId) {
        const tab = document.getElementById(tabId);
        if (tab) {
            bootstrap.Tab.getOrCreateInstance(tab).show();
        }
    }
});
</script>
@endpush
