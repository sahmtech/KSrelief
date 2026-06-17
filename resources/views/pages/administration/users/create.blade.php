@extends('layouts.admin')

@section('title', __('users.create_title'))

@section('content')
<x-page-header
    :title="__('users.create_title')"
    :subtitle="__('users.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('users.title'), 'url' => route('administration.users.index')],
        ['label' => __('users.create_title')],
    ]"
/>

<form method="POST" action="{{ route('administration.users.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-12">
            <x-card :title="__('users.sections.personal')">
                @include('pages.administration.users.partials.avatar-upload')

                <div class="row g-0 mt-2">
                    <div class="col-md-6 pe-md-2">
                        <x-form-input :label="__('users.fields.name')" name="name" :value="old('name')" :placeholder="__('users.placeholders.name')" required />
                    </div>
                    <div class="col-md-6 ps-md-2">
                        <x-form-input :label="__('users.fields.email')" name="email" type="email" :value="old('email')" :placeholder="__('users.placeholders.email')" required />
                    </div>
                </div>
                <div class="row g-0">
                    <div class="col-md-6 pe-md-2">
                        <x-form-input :label="__('users.fields.mobile')" name="mobile" :value="old('mobile')" :placeholder="__('users.placeholders.mobile')" required />
                    </div>
                    <div class="col-md-6 ps-md-2">
                        <x-form-input :label="__('users.fields.gender')" name="gender" type="select" required>
                            <option value="">{{ __('users.fields.gender') }}</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender->value }}" @selected(old('gender') === $gender->value)>{{ $gender->label() }}</option>
                            @endforeach
                        </x-form-input>
                    </div>
                </div>
                <div class="row g-0">
                    <div class="col-md-6 pe-md-2">
                        <x-form-input :label="__('users.fields.password')" name="password" type="password" required />
                    </div>
                    <div class="col-md-6 ps-md-2">
                        <x-form-input :label="__('users.fields.password_confirmation')" name="password_confirmation" type="password" required />
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-12">
            <x-card :title="__('users.sections.roles')">
                @include('pages.administration.users.partials.role-checkboxes', [
                    'roles' => $roles,
                    'selected' => old('roles', []),
                    'inputIdPrefix' => 'create_role',
                ])
            </x-card>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('administration.users.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
