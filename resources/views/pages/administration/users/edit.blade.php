@extends('layouts.admin')

@section('title', __('users.edit_title'))

@section('content')
<x-page-header
    :title="__('users.edit_title')"
    :subtitle="__('users.edit_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('users.title'), 'url' => route('administration.users.index')],
        ['label' => $user->name, 'url' => route('administration.users.show', $user)],
        ['label' => __('users.edit_title')],
    ]"
>
    <x-slot:actions>
        @can('view', $user)
            <a href="{{ route('administration.users.show', $user) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-eye me-1"></i> {{ __('users.actions.view') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<form method="POST" action="{{ route('administration.users.update', $user) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-12">
            <x-card :title="__('users.sections.personal')">
                @include('pages.administration.users.partials.avatar-upload', ['user' => $user])

                <div class="row g-0 mt-2">
                    <div class="col-md-6 pe-md-2">
                        <x-form-input :label="__('users.fields.name')" name="name" :value="old('name', $user->name)" required />
                    </div>
                    <div class="col-md-6 ps-md-2">
                        <x-form-input :label="__('users.fields.email')" name="email" type="email" :value="old('email', $user->email)" required />
                    </div>
                </div>
                <div class="row g-0">
                    <div class="col-md-6 pe-md-2">
                        <x-form-input :label="__('users.fields.mobile')" name="mobile" :value="old('mobile', $user->mobile)" required />
                    </div>
                    <div class="col-md-6 ps-md-2">
                        <x-form-input :label="__('users.fields.gender')" name="gender" type="select" required>
                            @foreach($genders as $gender)
                                <option value="{{ $gender->value }}" @selected(old('gender', $user->gender?->value) === $gender->value)>{{ $gender->label() }}</option>
                            @endforeach
                        </x-form-input>
                    </div>
                </div>
                <x-form-input :label="__('users.fields.status')" name="status" type="select" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $user->status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </x-form-input>
            </x-card>
        </div>
        <div class="col-12">
            <x-card :title="__('users.sections.roles')">
                @include('pages.administration.users.partials.role-checkboxes', [
                    'roles' => $roles,
                    'selected' => old('roles', $user->roles->pluck('name')->all()),
                    'inputIdPrefix' => 'edit_role',
                ])
            </x-card>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.save_changes') }}</button>
        <a href="{{ route('administration.users.show', $user) }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
