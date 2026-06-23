@extends('layouts.admin')

@section('title', $approach->name)

@section('content')
<x-page-header
    :title="__('settings.insertion_approaches.show_title')"
    :subtitle="__('settings.insertion_approaches.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.insertion_approaches.title'), 'url' => route('settings.insertion-approaches.index')],
        ['label' => $approach->name],
    ]"
>
    <x-slot:actions>
        @can('insertion_approach.update')<a href="{{ route('settings.insertion-approaches.edit', $approach) }}" class="btn btn-primary btn-sm"><i class="ti ti-pencil me-1"></i> {{ __('settings.actions.edit') }}</a>@endcan
    </x-slot:actions>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('settings.sections.details')">
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('settings.fields.name') }}</dt><dd class="col-sm-8">{{ $approach->name }}</dd>
                <dt class="col-sm-4">{{ __('settings.fields.code') }}</dt><dd class="col-sm-8"><code>{{ $approach->code }}</code></dd>
                <dt class="col-sm-4">{{ __('settings.fields.status') }}</dt>
                <dd class="col-sm-8"><span class="badge-status {{ $approach->status->badgeClass() }}">{{ $approach->status->label() }}</span></dd>
            </dl>
        </x-card>
    </div>
</div>
@endsection
