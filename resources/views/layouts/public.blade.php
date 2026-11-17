<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        .beneficiary-public {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 40%),
                linear-gradient(180deg, #f8fafc 0%, #eef6f4 100%);
        }
        .beneficiary-public__shell {
            max-width: 920px;
            margin: 0 auto;
            padding: 2rem 1rem 3rem;
        }
        .beneficiary-public__brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .beneficiary-public__brand img {
            height: 48px;
            width: auto;
        }
        .beneficiary-public .locale-switcher {
            flex-shrink: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
        }
        .beneficiary-public .locale-switcher__btn.active {
            background: #0F766E;
            color: #fff;
            box-shadow: none;
        }
        .beneficiary-wizard-steps {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }
        .beneficiary-wizard-steps .step-pill {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.85rem;
        }
        .beneficiary-wizard-steps .step-pill.active {
            background: #0F766E;
            border-color: #0F766E;
            color: #fff;
        }
        .beneficiary-wizard-steps .step-pill.done {
            background: #ccfbf1;
            border-color: #99f6e4;
            color: #0f766e;
        }
        .beneficiary-panel[hidden] { display: none !important; }
        .disease-row {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #fff;
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body class="beneficiary-public">
    <div class="beneficiary-public__shell">
        <div class="beneficiary-public__brand">
            <img src="{{ asset('images/esghaa-logo-horizontal.png') }}" alt="{{ config('app.name') }}">
            @include('layouts.partials.locale-switcher', ['class' => 'locale-switcher--inline'])
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
