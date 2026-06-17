@extends('layouts.admin')

@section('title', __('users.title'))

@section('content')
<x-page-header
    :title="__('users.title')"
    :subtitle="__('users.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('users.title')],
    ]"
>
    <x-slot:actions>
        @can('create', \App\Models\User::class)
            <a href="{{ route('administration.users.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> {{ __('users.add') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<x-card :title="__('users.filters.title')" :compact="true" class="mb-4">
    <form method="GET" action="{{ route('administration.users.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-group-admin__label">{{ __('users.filters.status') }}</label>
            <select name="status" class="form-group-admin__input">
                <option value="">{{ __('users.filters.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-group-admin__label">{{ __('users.filters.role') }}</label>
            <select name="role" class="form-group-admin__input">
                <option value="">{{ __('users.filters.all_roles') }}</option>
                @foreach($roles as $role)
                    @php $roleLabel = \App\Enums\SystemRole::tryFrom($role->name)?->label() ?? $role->name; @endphp
                    <option value="{{ $role->name }}" @selected($filters['role'] === $role->name)>
                        {{ $roleLabel }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">{{ __('users.filters.apply') }}</button>
            <a href="{{ route('administration.users.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('users.filters.reset') }}</a>
        </div>
    </form>
</x-card>

<x-card :flush="true">
    <x-datatable
        id="usersTable"
        :options="[
            'columnDefs' => [
                ['targets' => 7, 'orderable' => false, 'width' => '60px', 'className' => 'text-end'],
            ],
        ]"
    >
        <x-slot:head>
            <tr>
                <th>{{ __('users.table.name') }}</th>
                <th>{{ __('users.table.email') }}</th>
                <th>{{ __('users.table.mobile') }}</th>
                <th>{{ __('users.table.role') }}</th>
                <th>{{ __('users.table.status') }}</th>
                <th>{{ __('users.table.last_login') }}</th>
                <th>{{ __('users.table.created_at') }}</th>
                <th class="text-end">{{ __('users.table.actions') }}</th>
            </tr>
        </x-slot:head>
        @foreach($users as $user)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <x-user-avatar :user="$user" size="sm" />
                        <span class="fw-medium text-truncate">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="text-truncate" style="max-width: 180px;">{{ $user->email }}</td>
                <td>{{ $user->mobile ?? '—' }}</td>
                <td>
                    <span class="text-muted" style="font-size: 0.8125rem;">{{ $user->roleLabels() ?: '—' }}</span>
                </td>
                <td>
                    <span class="badge-status {{ $user->status->badgeClass() }}">{{ $user->status->label() }}</span>
                </td>
                <td>
                    {{ $user->last_login_at?->format('Y-m-d H:i') ?? __('users.messages.never_logged_in') }}
                </td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-label="{{ __('users.table.actions') }}">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            @can('view', $user)
                                <li><a class="dropdown-item" href="{{ route('administration.users.show', $user) }}"><i class="ti ti-eye me-2"></i>{{ __('users.actions.view') }}</a></li>
                            @endcan
                            @can('update', $user)
                                <li><a class="dropdown-item" href="{{ route('administration.users.edit', $user) }}"><i class="ti ti-pencil me-2"></i>{{ __('users.actions.edit') }}</a></li>
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changePasswordModal{{ $user->id }}">
                                        <i class="ti ti-key me-2"></i>{{ __('users.actions.change_password') }}
                                    </button>
                                </li>
                            @endcan
                            @can('activate', $user)
                                @if($user->status !== \App\Enums\UserStatus::Active)
                                    <li>
                                        <form method="POST" action="{{ route('administration.users.activate', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item text-success"><i class="ti ti-user-check me-2"></i>{{ __('users.actions.activate') }}</button>
                                        </form>
                                    </li>
                                @endif
                            @endcan
                            @can('deactivate', $user)
                                @if($user->status === \App\Enums\UserStatus::Active)
                                    <li>
                                        <form method="POST" action="{{ route('administration.users.deactivate', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item text-warning"><i class="ti ti-user-off me-2"></i>{{ __('users.actions.deactivate') }}</button>
                                        </form>
                                    </li>
                                @endif
                            @endcan
                            @can('delete', $user)
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('administration.users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" data-confirm="{{ __('users.messages.confirm_delete') }}">
                                            <i class="ti ti-trash me-2"></i>{{ __('users.actions.delete') }}
                                        </button>
                                    </form>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-datatable>
</x-card>

@foreach($users as $user)
    @can('changePassword', $user)
        <x-modal id="changePasswordModal{{ $user->id }}" :title="__('users.change_password.title')">
            <form method="POST" action="{{ route('administration.users.password.update', $user) }}" id="changePasswordForm{{ $user->id }}">
                @csrf
                @method('PUT')
                <x-form-input :label="__('users.fields.password')" name="password" type="password" required />
                <x-form-input :label="__('users.fields.password_confirmation')" name="password_confirmation" type="password" required />
            </form>
            <x-slot:footer>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="submit" form="changePasswordForm{{ $user->id }}" class="btn btn-primary">{{ __('users.change_password.submit') }}</button>
            </x-slot:footer>
        </x-modal>
    @endcan
@endforeach
@endsection
