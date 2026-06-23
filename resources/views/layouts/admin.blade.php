<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $htmlDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-locale" content="{{ $currentLocale }}">

    <title>@yield('title', __('menu.dashboard')) — {{ $adminName }}</title>

    @include('layouts.partials.favicon')
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body
    data-locale="{{ $currentLocale }}"
    data-i18n-success="{{ __('messages.success') }}"
    data-i18n-error="{{ __('messages.error') }}"
    data-i18n-confirm-title="{{ __('common.confirm.title') }}"
    data-i18n-confirm-message="{{ __('common.confirm.message') }}"
    data-i18n-confirm-yes="{{ __('common.confirm.yes') }}"
    data-i18n-cancel="{{ __('common.cancel') }}"
    data-dt-search="{{ __('common.datatable.search') }}"
    data-dt-search-placeholder="{{ __('common.datatable.search_placeholder') }}"
    data-dt-length-menu="{{ __('common.datatable.length_menu') }}"
    data-dt-info="{{ __('common.datatable.info') }}"
    data-dt-empty="{{ __('common.datatable.empty_table') }}"
    data-dt-zero="{{ __('common.datatable.zero_records') }}"
>
    @if(session('success'))
        <div data-flash-success="{{ session('success') }}" class="d-none"></div>
    @endif
    @if(session('status'))
        <div data-flash-success="{{ session('status') }}" class="d-none"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}" class="d-none"></div>
    @endif

    <div class="admin-wrapper">
        @include('layouts.partials.sidebar')

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="admin-main">
            @include('layouts.partials.navbar')

            <main class="admin-content">
                @yield('content')
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>

    @include('components.clinical-aud-fields-script')
    @stack('scripts')
</body>
</html>
