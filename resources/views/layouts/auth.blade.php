<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $htmlDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('auth.login')) — {{ $adminName }}</title>

    @include('layouts.partials.favicon')
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-wrapper__waves" aria-hidden="true">
            <svg viewBox="0 0 1440 320" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    fill="#CCFBF1"
                    d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                />
                <path
                    fill="#14B8A6"
                    fill-opacity="0.35"
                    d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,218.7C672,235,768,245,864,234.7C960,224,1056,192,1152,186.7C1248,181,1344,203,1392,213.3L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                />
                <path
                    fill="#0F766E"
                    fill-opacity="0.2"
                    d="M0,256L48,261.3C96,267,192,277,288,272C384,267,480,245,576,240C672,235,768,245,864,250.7C960,256,1056,256,1152,245.3C1248,235,1344,213,1392,202.7L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                />
            </svg>
        </div>

        <div class="auth-wrapper__container">
            <div class="auth-card auth-card--split">
                @include('layouts.partials.auth-brand')

                <div class="auth-card__panel">
                    @if (config('admin.show_locale_switcher'))
                    <div class="auth-card__panel-top">
                        @include('layouts.partials.locale-switcher', ['class' => 'locale-switcher--inline'])
                    </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
