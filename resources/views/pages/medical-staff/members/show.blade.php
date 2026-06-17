@extends('layouts.admin')

@section('title', $member->full_name)

@section('content')
<x-page-header
    :title="__('members.show_title')"
    :subtitle="__('members.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.medical_staff')],
        ['label' => __('members.title'), 'url' => route('medical-staff.members.index')],
        ['label' => $member->full_name],
    ]"
/>

<div class="user-profile-hero">
    <div class="user-profile-hero__banner"></div>
    <div class="user-profile-hero__body">
        <div class="user-profile-hero__header">
            <div class="user-profile-hero__identity">
                <div class="user-profile-hero__avatar">
                    <x-member-avatar :member="$member" size="xl" />
                </div>
                <div class="user-profile-hero__info">
                    <h1 class="user-profile-hero__name">{{ $member->full_name }}</h1>
                    <p class="user-profile-hero__email">{{ $member->memberRole?->name }} @if($member->specialty) · {{ $member->specialty->name }} @endif</p>
                    <div class="user-profile-hero__meta">
                        <span class="badge-status {{ $member->statusBadgeClass() }}">{{ $member->statusLabel() }}</span>
                        <span class="badge bg-light text-dark border">
                            <i class="ti ti-phone me-1"></i>{{ $member->mobile }}
                        </span>
                        @if($member->email)
                            <span class="badge bg-light text-dark border">
                                <i class="ti ti-mail me-1"></i>{{ $member->email }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="user-profile-hero__actions">
                @can('update', $member)
                    <a href="{{ route('medical-staff.members.edit', $member) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-pencil me-1"></i> {{ __('members.actions.edit') }}
                    </a>
                @endcan
                @can('assignCampaign', $member)
                    <a href="{{ route('medical-staff.members.campaigns', $member) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-flag me-1"></i> {{ __('members.actions.assign_campaigns') }}
                    </a>
                @endcan
                @can('delete', $member)
                    <form method="POST" action="{{ route('medical-staff.members.destroy', $member) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('members.messages.confirm_delete') }}">
                            <i class="ti ti-trash me-1"></i> {{ __('members.actions.delete') }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--primary"><i class="ti ti-flag"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ $stats['campaigns_count'] }}</div>
                <div class="user-stat-tile__label">{{ __('members.table.campaigns') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--success"><i class="ti ti-clipboard-check"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ $stats['attendance_count'] }}</div>
                <div class="user-stat-tile__label">{{ __('members.sections.attendance') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--secondary"><i class="ti ti-activity"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ $stats['activities_count'] }}</div>
                <div class="user-stat-tile__label">{{ __('members.sections.activity') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--warning"><i class="ti ti-calendar"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ $member->created_at->format('Y-m-d') }}</div>
                <div class="user-stat-tile__label">{{ __('members.fields.created_at') }}</div>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="memberTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab">
            <i class="ti ti-info-circle me-1"></i> {{ __('members.sections.personal') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button" role="tab">
            <i class="ti ti-clipboard-check me-1"></i> {{ __('members.sections.attendance') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $stats['attendance_count'] }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transportation-tab" data-bs-toggle="tab" data-bs-target="#transportation-pane" type="button" role="tab">
            <i class="ti ti-bus me-1"></i> {{ __('members.sections.transportation') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $stats['trips_count'] }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane" type="button" role="tab">
            <i class="ti ti-activity me-1"></i> {{ __('members.sections.activities') }}
            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $stats['activities_count'] }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="memberTabsContent">
<div class="tab-pane fade show active" id="overview-pane" role="tabpanel">
<div class="row g-3">
    <div class="col-lg-6">
        <x-card :title="__('members.sections.personal')">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('members.fields.gender') }}</div>
                    <div class="user-info-list__value">{{ $member->gender?->label() ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('members.fields.date_of_birth') }}</div>
                    <div class="user-info-list__value">{{ $member->date_of_birth?->format('Y-m-d') ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('members.fields.age') }}</div>
                    <div class="user-info-list__value">{{ $member->age ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('members.fields.nationality') }}</div>
                    <div class="user-info-list__value">{{ $member->nationality ?? '—' }}</div>
                </div>
            </div>
        </x-card>

        <x-card :title="__('members.sections.professional')" class="mt-3">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('members.fields.role') }}</div>
                    <div class="user-info-list__value">{{ $member->memberRole?->name ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('members.fields.specialty') }}</div>
                    <div class="user-info-list__value">{{ $member->specialty?->name ?? '—' }}</div>
                </div>
                @if($member->notes)
                    <div class="user-info-list__item">
                        <div class="user-info-list__label">{{ __('members.fields.notes') }}</div>
                        <div class="user-info-list__value">{{ $member->notes }}</div>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('members.sections.account')">
            @if($member->user)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <x-user-avatar :user="$member->user" size="md" />
                    <div>
                        <div class="fw-medium">{{ $member->user->name }}</div>
                        <div class="text-muted" style="font-size: 0.8125rem;">{{ $member->user->email }}</div>
                    </div>
                </div>
                <a href="{{ route('administration.users.show', $member->user) }}" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-external-link me-1"></i> {{ __('members.actions.view') }}
                </a>
            @else
                <div class="text-center text-muted py-3">
                    <i class="ti ti-user-off d-block mb-2" style="font-size: 2rem; opacity: 0.4;"></i>
                    {{ __('members.fields.no_user') }}
                </div>
            @endif
        </x-card>
    </div>

    <div class="col-12">
        <x-card :title="__('members.sections.campaigns')" :flush="true">
            @if($member->campaignAssignments->isEmpty())
                <div class="text-center text-muted py-4">{{ __('members.campaigns_page.no_assigned') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('members.fields.campaign') }}</th>
                                <th>{{ __('members.fields.assigned_role') }}</th>
                                <th>{{ __('members.fields.assigned_from') }}</th>
                                <th>{{ __('members.fields.assigned_to') }}</th>
                                <th class="text-end">{{ __('members.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->campaignAssignments as $assignment)
                                <tr>
                                    <td>
                                        <a href="{{ route('campaigns.show', $assignment->campaign) }}" class="fw-medium text-decoration-none">
                                            {{ $assignment->campaign->name }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            {{ $assignment->campaign->country?->localizedName() }} · {{ $assignment->campaign->city?->localizedName() }}
                                        </div>
                                    </td>
                                    <td>{{ $assignment->assigned_role ?? $member->memberRole?->name ?? '—' }}</td>
                                    <td>{{ $assignment->assigned_from?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $assignment->assigned_to?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="text-end">
                                        @can('assignCampaign', $member)
                                            <form method="POST" action="{{ route('medical-staff.members.campaigns.remove', [$member, $assignment->campaign]) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('members.messages.confirm_remove') }}">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                        @endcan
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
</div>

<div class="tab-pane fade" id="attendance-pane" role="tabpanel">
    @include('pages.medical-staff.members.partials.attendance-tab', [
        'member' => $member,
        'attendanceStats' => $attendanceStats,
        'recentAttendances' => $recentAttendances,
    ])
</div>

<div class="tab-pane fade" id="transportation-pane" role="tabpanel">
    @include('pages.medical-staff.members.partials.transportation-tab', [
        'member' => $member,
        'transportStats' => $transportStats,
        'memberTrips' => $memberTrips,
    ])
</div>

<div class="tab-pane fade" id="activities-pane" role="tabpanel">
    @include('pages.medical-staff.members.partials.activities-tab', [
        'member' => $member,
        'activityStats' => $activityStats,
        'memberActivities' => $memberActivities,
    ])
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    if (hash === '#attendance' || hash === '#transportation' || hash === '#activities') {
        const tab = document.getElementById(hash.slice(1) + '-tab');
        if (tab) {
            bootstrap.Tab.getOrCreateInstance(tab).show();
        }
    }
});
</script>
@endpush
