@extends('layouts.admin')

@section('title', $company->name)

@section('content')
<x-page-header
    :title="__('settings.implant_companies.show_title')"
    :subtitle="__('settings.implant_companies.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.implant_companies.title'), 'url' => route('settings.implant-companies.index')],
        ['label' => $company->name],
    ]"
>
    <x-slot:actions>
        @can('implant_company.update')<a href="{{ route('settings.implant-companies.edit', $company) }}" class="btn btn-primary btn-sm"><i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt>
                <dd class="col-sm-8 fw-semibold" style="color: {{ $company->color }};">{{ $company->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.code') }}</dt><dd class="col-sm-8"><code>{{ $company->code }}</code></dd>
                <dt class="col-sm-4">{{ __('settings.fields.color') }}</dt>
                <dd class="col-sm-8">
                    <span class="d-inline-flex align-items-center gap-2">
                        <span class="rounded-circle d-inline-block" style="width: 16px; height: 16px; background-color: {{ $company->color }};"></span>
                        <code>{{ $company->color }}</code>
                    </span>
                </dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8"><span class="badge-status {{ $company->status->badgeClass() }}">{{ $company->status->label() }}</span></dd>
            </dl>
        </x-card>

        <x-card :title="__('settings.implant_companies.electrode_types')" class="mt-3">
            @if($company->electrodeTypes->isEmpty())
                <p class="text-muted mb-0">{{ __('common.no_records') }}</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($company->electrodeTypes as $electrode)
                        <li class="list-group-item px-0" style="color: {{ $company->color }};">{{ $electrode->name }}</li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('settings.sections.audit')">
            <dl class="row mb-0">
                <dt class="col-sm-5">{{ __('settings.fields.created_at') }}</dt><dd class="col-sm-7">{{ $company->created_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-sm-5">{{ __('settings.fields.updated_at') }}</dt><dd class="col-sm-7">{{ $company->updated_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
