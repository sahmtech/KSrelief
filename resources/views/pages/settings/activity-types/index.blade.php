@extends('layouts.admin')

@section('title', __('settings.activity_types.title'))

@section('content')
<x-page-header
    :title="__('settings.activity_types.title')"
    :subtitle="__('settings.activity_types.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings')],
        ['label' => __('settings.dashboard.title'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.activity_types.title')],
    ]"
>
    <x-slot:actions>
        @can('activity_type.create')
            <a href="{{ route('settings.activity-types.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> {{ __('settings.activity_types.add') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<x-card :title="__('settings.filters.title')" :compact="true" class="mb-4">
    <form method="GET" action="{{ route('settings.activity-types.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('settings.activity-types.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('settings.filters.reset') }}</a>
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
                    <th>{{ __('settings.table.color') }}</th>
                    <th>{{ __('settings.table.status') }}</th>
                    <th class="text-end">{{ __('settings.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activityTypes as $activityType)
                    <tr>
                        <td class="fw-medium">{{ $activityType->name }}</td>
                        <td><code>{{ $activityType->code }}</code></td>
                        <td>
                            <span class="d-inline-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background-color: {{ $activityType->color }};"></span>
                                <code>{{ $activityType->color }}</code>
                            </span>
                        </td>
                        <td><span class="badge-status {{ $activityType->status->badgeClass() }}">{{ $activityType->status->label() }}</span></td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-label="{{ __('settings.table.actions') }}"><i class="ti ti-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    @can('activity_type.view')<li><a class="dropdown-item" href="{{ route('settings.activity-types.show', $activityType) }}"><i class="ti ti-eye me-2"></i>{{ __('settings.actions.view') }}</a></li>@endcan
                                    @can('activity_type.update')<li><a class="dropdown-item" href="{{ route('settings.activity-types.edit', $activityType) }}"><i class="ti ti-pencil me-2"></i>{{ __('settings.actions.edit') }}</a></li>@endcan
                                    @can('activity_type.delete')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><form method="POST" action="{{ route('settings.activity-types.destroy', $activityType) }}">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('settings.messages.confirm_delete') }}"><i class="ti ti-trash me-2"></i>{{ __('settings.actions.delete') }}</button></form></li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('settings.messages.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($activityTypes->hasPages())<div class="admin-card__footer">{{ $activityTypes->links() }}</div>@endif
</x-card>
@endsection
