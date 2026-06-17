@extends('layouts.admin')

@section('title', __('members.campaigns_page.title'))

@section('content')
<x-page-header
    :title="__('members.campaigns_page.title')"
    :subtitle="__('members.campaigns_page.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.medical_staff')],
        ['label' => __('members.title'), 'url' => route('medical-staff.members.index')],
        ['label' => $member->full_name, 'url' => route('medical-staff.members.show', $member)],
        ['label' => __('members.campaigns_page.title')],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('medical-staff.members.show', $member) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i> {{ __('members.actions.view') }}
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <x-card :compact="true">
            <div class="d-flex align-items-center gap-3">
                <x-member-avatar :member="$member" size="lg" />
                <div>
                    <div class="fw-semibold">{{ $member->full_name }}</div>
                    <div class="text-muted" style="font-size: 0.8125rem;">{{ $member->memberRole?->name }}</div>
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <x-card :title="__('members.campaigns_page.assigned')" :flush="true">
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
                                        <a href="{{ route('campaigns.show', $assignment->campaign) }}" class="text-decoration-none fw-medium">
                                            {{ $assignment->campaign->name }}
                                        </a>
                                    </td>
                                    <td>{{ $assignment->assigned_role ?? '—' }}</td>
                                    <td>{{ $assignment->assigned_from?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $assignment->assigned_to?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('medical-staff.members.campaigns.remove', [$member, $assignment->campaign]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('members.messages.confirm_remove') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <div class="col-lg-5">
        <x-card :title="__('members.actions.assign')">
            @if($campaigns->isEmpty())
                <div class="text-muted text-center py-3" style="font-size: 0.875rem;">{{ __('members.campaigns_page.no_available') }}</div>
            @else
                <form method="POST" action="{{ route('medical-staff.members.campaigns.assign', $member) }}">
                    @csrf
                    <x-form-input :label="__('members.fields.campaign')" name="campaign_id" type="select" required>
                        <option value="">{{ __('members.placeholders.select_campaign') }}</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" @selected((string) old('campaign_id') === (string) $campaign->id)>
                                {{ $campaign->name }} ({{ $campaign->country?->localizedName() }})
                            </option>
                        @endforeach
                    </x-form-input>
                    <x-form-input :label="__('members.fields.assigned_role')" name="assigned_role" :value="old('assigned_role', $member->memberRole?->name)" />
                    <div class="row g-0">
                        <div class="col-md-6 pe-md-2">
                            <x-form-input :label="__('members.fields.assigned_from')" name="assigned_from" type="date" :value="old('assigned_from', now()->toDateString())" />
                        </div>
                        <div class="col-md-6 ps-md-2">
                            <x-form-input :label="__('members.fields.assigned_to')" name="assigned_to" type="date" :value="old('assigned_to')" />
                        </div>
                    </div>
                    <x-form-input :label="__('members.fields.notes')" name="notes" type="textarea" :value="old('notes')" />
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="ti ti-plus me-1"></i> {{ __('members.actions.assign') }}
                    </button>
                </form>
            @endif
        </x-card>
    </div>
</div>
@endsection
