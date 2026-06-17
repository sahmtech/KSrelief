@extends('layouts.auth')

@section('title', __('auth.reset_password'))

@section('content')
<div class="auth-card__header">
    <h2>{{ __('auth.reset_password') }}</h2>
    <p>{{ __('auth.reset_password_subtitle') }}</p>
</div>

<form method="POST" action="{{ route('password.update') }}" class="auth-card__form">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <x-form-input
        :label="__('auth.email')"
        name="email"
        type="email"
        :value="old('email', $email)"
        required
        autofocus
    />

    <x-form-input
        :label="__('auth.new_password')"
        name="password"
        type="password"
        :placeholder="__('auth.new_password_placeholder')"
        required
    />

    <x-form-input
        :label="__('auth.confirm_password')"
        name="password_confirmation"
        type="password"
        :placeholder="__('auth.confirm_password_placeholder')"
        required
    />

    <button type="submit" class="btn btn-primary">{{ __('auth.reset_password') }}</button>
</form>

<div class="auth-card__footer">
    <a href="{{ route('login') }}"><i class="ti ti-arrow-left me-1"></i>{{ __('auth.back_to_sign_in') }}</a>
</div>
@endsection
