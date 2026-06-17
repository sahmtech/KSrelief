@extends('layouts.admin')

@section('title', $user->name)

@section('content')
<x-page-header
    :title="__('users.show_title')"
    :subtitle="__('users.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('users.title'), 'url' => route('administration.users.index')],
        ['label' => $user->name],
    ]"
/>

{{-- Profile Hero --}}
<div class="user-profile-hero">
    <div class="user-profile-hero__banner"></div>
    <div class="user-profile-hero__body">
        <div class="user-profile-hero__header">
            <div class="user-profile-hero__identity">
                <div class="user-profile-hero__avatar">
                    <x-user-avatar :user="$user" size="xl" />
                </div>
                <div class="user-profile-hero__info">
                    <h1 class="user-profile-hero__name">{{ $user->name }}</h1>
                    <p class="user-profile-hero__email">{{ $user->email }}</p>
                    <div class="user-profile-hero__meta">
                        <span class="badge-status {{ $user->status->badgeClass() }}">{{ $user->status->label() }}</span>
                        <span class="badge bg-light text-dark border">
                            <i class="ti ti-id me-1"></i>{{ __('users.show.user_id') }} #{{ $user->id }}
                        </span>
                        @if($user->gender)
                            <span class="badge bg-light text-dark border">
                                <i class="ti ti-gender-{{ $user->gender->value === 'male' ? 'male' : 'female' }} me-1"></i>
                                {{ $user->gender->label() }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="user-profile-hero__actions">
                @can('update', $user)
                    <a href="{{ route('administration.users.edit', $user) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-pencil me-1"></i> {{ __('users.actions.edit') }}
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="ti ti-key me-1"></i> {{ __('users.actions.change_password') }}
                    </button>
                @endcan
                @can('activate', $user)
                    @if($user->status !== \App\Enums\UserStatus::Active)
                        <form method="POST" action="{{ route('administration.users.activate', $user) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-success btn-sm">
                                <i class="ti ti-user-check me-1"></i> {{ __('users.actions.activate') }}
                            </button>
                        </form>
                    @endif
                @endcan
                @can('deactivate', $user)
                    @if($user->status === \App\Enums\UserStatus::Active)
                        <form method="POST" action="{{ route('administration.users.deactivate', $user) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                <i class="ti ti-user-off me-1"></i> {{ __('users.actions.deactivate') }}
                            </button>
                        </form>
                    @endif
                @endcan
                @can('delete', $user)
                    <form method="POST" action="{{ route('administration.users.destroy', $user) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="{{ __('users.messages.confirm_delete') }}">
                            <i class="ti ti-trash me-1"></i> {{ __('users.actions.delete') }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        @if($user->roles->isNotEmpty())
            <div class="user-profile-hero__roles">
                @foreach($user->roles as $role)
                    <span class="user-profile-hero__role-chip">
                        <i class="ti ti-shield"></i>
                        {{ \App\Enums\SystemRole::tryFrom($role->name)?->label() ?? $role->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--primary"><i class="ti ti-shield"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ $stats['roles_count'] }}</div>
                <div class="user-stat-tile__label">{{ __('users.show.roles_assigned') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--secondary"><i class="ti ti-lock"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ $stats['permissions_count'] }}</div>
                <div class="user-stat-tile__label">{{ __('users.show.permissions_granted') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--success"><i class="ti ti-calendar"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ (int) $stats['member_days'] }}</div>
                <div class="user-stat-tile__label">{{ __('users.show.member_since') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--warning"><i class="ti ti-login"></i></div>
            <div>
                <div class="user-stat-tile__value" style="font-size: 1rem;">
                    {{ $user->last_login_at?->format('M d, Y') ?? '—' }}
                </div>
                <div class="user-stat-tile__label">{{ __('users.fields.last_login') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Contact & Timeline --}}
    <div class="col-lg-4">
        <x-card :title="__('users.show.contact_info')">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__icon"><i class="ti ti-mail"></i></div>
                    <div class="user-info-list__content">
                        <div class="user-info-list__label">{{ __('users.fields.email') }}</div>
                        <div class="user-info-list__value">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__icon"><i class="ti ti-phone"></i></div>
                    <div class="user-info-list__content">
                        <div class="user-info-list__label">{{ __('users.fields.mobile') }}</div>
                        <div class="user-info-list__value">{{ $user->mobile ?? '—' }}</div>
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__icon"><i class="ti ti-user"></i></div>
                    <div class="user-info-list__content">
                        <div class="user-info-list__label">{{ __('users.fields.gender') }}</div>
                        <div class="user-info-list__value">{{ $user->gender?->label() ?? '—' }}</div>
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__icon"><i class="ti ti-circle-check"></i></div>
                    <div class="user-info-list__content">
                        <div class="user-info-list__label">{{ __('users.fields.email_verified') }}</div>
                        <div class="user-info-list__value">
                            @if($user->email_verified_at)
                                {{ __('users.show.email_verified_at', ['date' => $user->email_verified_at->format('Y-m-d')]) }}
                            @else
                                <span class="text-warning">{{ __('users.show.not_verified') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card :title="__('users.show.account_timeline')" class="mt-3">
            <div class="user-timeline">
                <div class="user-timeline__item">
                    <div class="user-timeline__label">{{ __('users.fields.created_at') }}</div>
                    <div class="user-timeline__value">{{ $user->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="user-timeline__item">
                    <div class="user-timeline__label">{{ __('users.fields.updated_at') }}</div>
                    <div class="user-timeline__value">{{ $user->updated_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="user-timeline__item">
                    <div class="user-timeline__label">{{ __('users.fields.last_login') }}</div>
                    <div class="user-timeline__value">
                        {{ $user->last_login_at?->format('Y-m-d H:i') ?? __('users.messages.never_logged_in') }}
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Roles Detail --}}
    <div class="col-lg-8">
        <x-card :title="__('users.sections.roles')">
            @if($user->roles->isEmpty())
                <p class="text-muted mb-0">—</p>
            @else
                <div class="row g-3">
                    @foreach($user->roles as $role)
                        @php
                            $roleLabel = \App\Enums\SystemRole::tryFrom($role->name)?->label() ?? $role->name;
                            $permCount = $role->permissions->count();
                        @endphp
                        <div class="col-md-6">
                            <div class="user-role-card">
                                <div class="user-role-card__header">
                                    <div class="user-role-card__icon"><i class="ti ti-shield"></i></div>
                                    <div>
                                        <h4 class="user-role-card__title">{{ $roleLabel }}</h4>
                                        <div class="user-role-card__slug"><code>{{ $role->name }}</code></div>
                                    </div>
                                </div>
                                <div class="user-role-card__count">
                                    <i class="ti ti-lock me-1"></i>
                                    {{ trans_choice('users.show.permissions_count_label', $permCount, ['count' => $permCount]) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card :title="__('users.sections.permissions')" class="mt-3">
            @if($groupedPermissions->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="ti ti-lock-off d-block mb-2" style="font-size: 2rem; opacity: 0.4;"></i>
                    {{ __('users.messages.no_permissions') }}
                </div>
            @else
                @foreach($groupedPermissions as $group)
                    <div class="permission-group">
                        <div class="permission-group__header">
                            <h4 class="permission-group__title">{{ $group['label'] }}</h4>
                            <span class="permission-group__coverage">
                                {{ __('users.show.coverage', [
                                    'granted' => $group['granted']->count(),
                                    'total' => $group['total'],
                                ]) }}
                            </span>
                        </div>
                        <div class="permission-group__body">
                            @foreach($group['granted'] as $permission)
                                @php $permKey = str_replace('.', '_', $permission->name); @endphp
                                <span class="permission-group__tag" title="{{ $permission->name }}">
                                    <i class="ti ti-check"></i>
                                    {{ __('permissions.names.'.$permKey) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </x-card>
    </div>
</div>

@include('pages.administration.users.partials.change-password-modal', ['user' => $user])
@endsection
