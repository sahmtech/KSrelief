@extends('layouts.auth')

@section('title', __('auth.forgot_password_title'))

@section('content')
<div class="auth-card__header">
    <h2>{{ __('auth.forgot_password_title') }}</h2>
    <p>{{ __('auth.forgot_password_subtitle') }}</p>
</div>

<form method="POST" action="{{ route('password.email') }}" class="auth-card__form">
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

    <button type="submit" class="btn btn-primary">{{ __('auth.send_reset_link') }}</button>
</form>

<div class="auth-card__footer">
    <a href="{{ route('login') }}"><i class="ti ti-arrow-left me-1"></i>{{ __('auth.back_to_sign_in') }}</a>
</div>
@endsection
