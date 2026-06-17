@extends('layouts.admin')

@section('title', $campaignStatus->name)

@section('content')
<x-page-header
    :title="__('settings.campaign_statuses.show_title')"
    :subtitle="__('settings.campaign_statuses.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.campaign_statuses.title'), 'url' => route('settings.campaign-statuses.index')],
        ['label' => $campaignStatus->name],
    ]"
>
    <x-slot:actions>
        @can('campaign_status.update')<a href="{{ route('settings.campaign-statuses.edit', $campaignStatus) }}" class="btn btn-primary btn-sm"><i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}</a>@endcan
        @can('campaign_status.delete')
            <form method="POST" action="{{ route('settings.campaign-statuses.destroy', $campaignStatus) }}" class="d-inline">@csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('settings.messages.confirm_delete') }}"><i class="ti ti-trash me-1"></i> {{ __('settings.actions.delete') }}</button>
            </form>
        @endcan
    </x-slot:actions>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt><dd class="col-sm-8">{{ $campaignStatus->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.code') }}</dt><dd class="col-sm-8"><code>{{ $campaignStatus->code }}</code></dd>
                <dt class="col-sm-4">{{ __('settings.fields.color') }}</dt>
                <dd class="col-sm-8">
                    <span class="d-inline-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 16px; height: 16px; background-color: {{ $campaignStatus->color }};"></span>
                        <code>{{ $campaignStatus->color }}</code>
                    </span>
                </dd>
                <dt class="col-sm-4">{{ __('settings.fields.is_default') }}</dt><dd class="col-sm-8">{{ $campaignStatus->is_default ? __('settings.messages.yes') : __('settings.messages.no') }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8"><span class="badge-status {{ $campaignStatus->status->badgeClass() }}">{{ $campaignStatus->status->label() }}</span></dd>
            </dl>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('settings.sections.audit')">
            <dl class="row mb-0">
                <dt class="col-sm-5">{{ __('settings.fields.created_at') }}</dt><dd class="col-sm-7">{{ $campaignStatus->created_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_at') }}</dt><dd class="col-sm-7">{{ $campaignStatus->updated_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.created_by') }}</dt><dd class="col-sm-7">{{ $campaignStatus->creator?->name ?? '—' }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_by') }}</dt><dd class="col-sm-7">{{ $campaignStatus->updater?->name ?? '—' }}</dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
