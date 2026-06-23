@extends('layouts.admin')

@section('title', $option->name)

@section('content')
<x-page-header
    :title="__('settings.mri_finding_options.show_title')"
    :subtitle="__('settings.mri_finding_options.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.mri_finding_options.title'), 'url' => route('settings.mri-finding-options.index')],
        ['label' => $option->name],
    ]"
>
    <x-slot:actions>
        @can('mri_finding_option.update')<a href="{{ route('settings.mri-finding-options.edit', $option) }}" class="btn btn-primary btn-sm"><i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt><dd class="col-sm-8">{{ $option->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.code') }}</dt><dd class="col-sm-8"><code>{{ $option->code }}</code></dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8"><span class="badge-status {{ $option->status->badgeClass() }}">{{ $option->status->label() }}</span></dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
