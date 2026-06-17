@extends('layouts.admin')

@section('title', $country->localizedName())

@section('content')
<x-page-header
    :title="__('settings.countries.show_title')"
    :subtitle="__('settings.countries.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.countries.title'), 'url' => route('settings.countries.index')],
        ['label' => $country->localizedName()],
    ]"
>
    <x-slot:actions>
        @can('country.update')
            <a href="{{ route('settings.countries.edit', $country) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}
            </a>
        @endcan
        @can('country.delete')
            <form method="POST" action="{{ route('settings.countries.destroy', $country) }}" class="d-inline">
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
                <dd class="col-sm-8">{{ $country->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.name_ar') }}</dt>
                <dd class="col-sm-8">{{ $country->name_ar ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.code') }}</dt>
                <dd class="col-sm-8"><code>{{ $country->code }}</code></dd>
                <dt class="col-sm-4">{{ __('settings.fields.iso2') }}</dt>
                <dd class="col-sm-8">{{ $country->iso2 ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.iso3') }}</dt>
                <dd class="col-sm-8">{{ $country->iso3 ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.phone_code') }}</dt>
                <dd class="col-sm-8">{{ $country->phone_code ?? '—' }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8">
                    <span class="badge-status {{ $country->status->badgeClass() }}">{{ $country->status->label() }}</span>
                </dd>
            </dl>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('settings.sections.audit')">
            <dl class="row mb-0">
                <dt class="col-sm-5">{{ __('settings.fields.created_at') }}</dt>
                <dd class="col-sm-7">{{ $country->created_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_at') }}</dt>
                <dd class="col-sm-7">{{ $country->updated_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.created_by') }}</dt>
                <dd class="col-sm-7">{{ $country->creator?->name ?? '—' }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_by') }}</dt>
                <dd class="col-sm-7">{{ $country->updater?->name ?? '—' }}</dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
