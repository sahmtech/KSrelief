@extends('layouts.admin')

@section('title', $city->localizedName())

@section('content')
<x-page-header
    :title="__('settings.cities.show_title')"
    :subtitle="__('settings.cities.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.cities.title'), 'url' => route('settings.cities.index')],
        ['label' => $city->localizedName()],
    ]"
>
    <x-slot:actions>
        @can('city.update')
            <a href="{{ route('settings.cities.edit', $city) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}
            </a>
        @endcan
        @can('city.delete')
            <form method="POST" action="{{ route('settings.cities.destroy', $city) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('settings.messages.confirm_delete') }}">
                    <i class="ti ti-trash me-1"></i> {{ __('settings.actions.delete') }}
                </button>
            </form>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt>
                <dd class="col-sm-8">{{ $city->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.name_ar') }}</dt>
                <dd class="col-sm-8">{{ $city->name_ar ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.country') }}</dt>
                <dd class="col-sm-8">{{ $city->country?->localizedName() ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8">
                    <span class="badge-status {{ $city->status->badgeClass() }}">{{ $city->status->label() }}</span>
                </dd>
            </dl>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('settings.sections.audit')">
            <dl class="row mb-0">
                <dt class="col-sm-5">{{ __('settings.fields.created_at') }}</dt>
                <dd class="col-sm-7">{{ $city->created_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_at') }}</dt>
                <dd class="col-sm-7">{{ $city->updated_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.created_by') }}</dt>
                <dd class="col-sm-7">{{ $city->creator?->name ?? '—' }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_by') }}</dt>
                <dd class="col-sm-7">{{ $city->updater?->name ?? '—' }}</dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
