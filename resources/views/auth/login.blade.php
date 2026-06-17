@extends('layouts.auth')

@section('title', __('auth.login'))

@section('content')
<div class="auth-card__header">
    <h2>{{ __('auth.login') }}</h2>
    <p>{{ __('auth.sign_in_subtitle', ['name' => $adminName]) }}</p>
</div>

<form method="POST" action="{{ route('login') }}" class="auth-card__form">
    @csrf

    <x-form-input
        :label="__('auth.email')"
        name="email"
        type="email"
        :value="old('email')"
        :placeholder="__('auth.email_placeholder')"
        required
        autofocus
    />

    <x-form-input
        :label="__('auth.password')"
        name="password"
        type="password"
        :placeholder="__('auth.password_placeholder')"
        required
    />

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                name="remember"
                id="remember"
                {{ old('remember') ? 'checked' : '' }}
            >
            <label class="form-check-label" for="remember">
                {{ __('auth.remember_me') }}
            </label>
        </div>
        <a href="{{ route('password.request') }}" class="auth-card__link">{{ __('auth.forgot_password') }}</a>
    </div>

    <button type="submit" class="btn btn-primary">{{ __('auth.login') }}</button>
</form>
@endsection
