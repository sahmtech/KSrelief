@extends('layouts.admin')

@section('title', __('settings.insertion_approaches.title'))

@section('content')
<x-page-header
    :title="__('settings.insertion_approaches.title')"
    :subtitle="__('settings.insertion_approaches.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.insertion_approaches.title')],
    ]"
>
    <x-slot:actions>
        @can('insertion_approach.create')
            <a href="{{ route('settings.insertion-approaches.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> {{ __('settings.insertion_approaches.add') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<x-card :title="__('settings.filters.title')" :compact="true" class="mb-4">
    <form method="GET" action="{{ route('settings.insertion-approaches.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-group-admin__label">{{ __('settings.filters.search') }}</label>
            <input type="search" name="search" class="form-group-admin__input" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('settings.filters.search_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="form-group-admin__label">{{ __('settings.filters.status') }}</label>
            <select name="status" class="form-group-admin__input">
                <option value="">{{ __('settings.filters.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('settings.filters.apply') }}</button>
            <a href="{{ route('settings.insertion-approaches.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('settings.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('settings.table.name') }}</th>
                    <th>{{ __('settings.table.code') }}</th>
                    <th>{{ __('settings.table.status') }}</th>
                    <th class="text-end">{{ __('settings.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approaches as $approach)
                    <tr>
                        <td class="fw-medium">{{ $approach->name }}</td>
                        <td><code>{{ $approach->code }}</code></td>
                        <td><span class="badge-status {{ $approach->status->badgeClass() }}">{{ $approach->status->label() }}</span></td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    @can('insertion_approach.view')<li><a class="dropdown-item" href="{{ route('settings.insertion-approaches.show', $approach) }}"><i class="ti ti-eye me-2"></i>{{ __('settings.actions.view') }}</a></li>@endcan
                                    @can('insertion_approach.update')<li><a class="dropdown-item" href="{{ route('settings.insertion-approaches.edit', $approach) }}"><i class="ti ti-pencil me-2"></i>{{ __('settings.actions.edit') }}</a></li>@endcan
                                    @can('insertion_approach.delete')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><form method="POST" action="{{ route('settings.insertion-approaches.destroy', $approach) }}">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('settings.messages.confirm_delete') }}"><i class="ti ti-trash me-2"></i>{{ __('settings.actions.delete') }}</button></form></li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('common.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($approaches->hasPages())
        <div class="card-footer bg-white border-top">{{ $approaches->links() }}</div>
    @endif
</x-card>
@endsection
