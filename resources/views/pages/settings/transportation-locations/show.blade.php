@extends('layouts.admin')

@section('title', $transportationLocation->name)

@section('content')
<x-page-header
    :title="__('settings.transportation_locations.show_title')"
    :subtitle="__('settings.transportation_locations.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.transportation_locations.title'), 'url' => route('settings.transportation-locations.index')],
        ['label' => $transportationLocation->name],
    ]"
>
    <x-slot:actions>
        @can('transport_location.update')<a href="{{ route('settings.transportation-locations.edit', $transportationLocation) }}" class="btn btn-primary btn-sm"><i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}</a>@endcan
        @can('transport_location.delete')
            <form method="POST" action="{{ route('settings.transportation-locations.destroy', $transportationLocation) }}" class="d-inline">@csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('settings.messages.confirm_delete') }}"><i class="ti ti-trash me-1"></i> {{ __('settings.actions.delete') }}</button>
            </form>
        @endcan
    </x-slot:actions>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt><dd class="col-sm-8">{{ $transportationLocation->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.type') }}</dt><dd class="col-sm-8">{{ __('settings.transportation_types.'.$transportationLocation->type) }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.description') }}</dt><dd class="col-sm-8">{{ $transportationLocation->description ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8"><span class="badge-status {{ $transportationLocation->status->badgeClass() }}">{{ $transportationLocation->status->label() }}</span></dd>
            </dl>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('settings.sections.audit')">
            <dl class="row mb-0">
                <dt class="col-sm-5">{{ __('settings.fields.created_at') }}</dt><dd class="col-sm-7">{{ $transportationLocation->created_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_at') }}</dt><dd class="col-sm-7">{{ $transportationLocation->updated_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.created_by') }}</dt><dd class="col-sm-7">{{ $transportationLocation->creator?->name ?? '—' }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_by') }}</dt><dd class="col-sm-7">{{ $transportationLocation->updater?->name ?? '—' }}</dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
