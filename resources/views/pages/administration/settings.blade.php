@extends('layouts.admin')

@section('title', __('pages.settings.title'))

@section('content')
<x-page-header
    :title="__('pages.settings.title')"
    :subtitle="__('pages.settings.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('pages.settings.title')],
    ]"
/>

<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('pages.settings.general')">
            <x-form-input :label="__('pages.settings.org_name')" name="org_name" :value="config('admin.name')" />
            <x-form-input :label="__('pages.settings.contact_email')" name="contact_email" type="email" :placeholder="__('pages.settings.contact_email_placeholder')" />
            <x-form-input :label="__('pages.settings.timezone')" name="timezone" type="select">
                <option value="UTC">UTC</option>
                <option value="Asia/Riyadh" selected>Asia/Riyadh (GMT+3)</option>
                <option value="Asia/Amman">Asia/Amman (GMT+3)</option>
            </x-form-input>
            <button type="button" class="btn btn-primary">{{ __('common.save_changes') }}</button>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card :title="__('pages.settings.account')">
            <div class="d-flex align-items-center gap-3 mb-3">
                <x-user-avatar :user="auth()->user()" size="md" />
                <div>
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size: 0.8125rem;">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm w-100">{{ __('common.change_password') }}</button>
        </x-card>
    </div>
</div>
@endsection
