<header class="admin-navbar">
    <button type="button" class="admin-navbar__toggle" id="sidebarToggle" aria-label="{{ __('layout.toggle_sidebar') }}">
        <i class="ti ti-menu-2"></i>
    </button>

    @can('viewAny', \App\Models\Patient::class)
    <div class="admin-navbar__search patient-search" data-patient-search data-search-url="{{ route('patients.search') }}" data-empty-text="{{ __('layout.patient_search_empty') }}" data-loading-text="{{ __('layout.patient_search_loading') }}">
        <i class="ti ti-search"></i>
        <input
            type="search"
            data-patient-search-input
            placeholder="{{ __('layout.patient_search_placeholder') }}"
            aria-label="{{ __('layout.patient_search') }}"
            aria-expanded="false"
            aria-autocomplete="list"
            role="combobox"
            autocomplete="off"
        >
        <div class="patient-search__results" data-patient-search-results hidden role="listbox"></div>
    </div>
    @endcan

    <div class="admin-navbar__actions">
        @if (config('admin.show_locale_switcher'))
        {{-- Language Switcher --}}
        <div class="dropdown">
            <button
                type="button"
                class="admin-navbar__action-btn"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="{{ __('layout.language') }}"
                title="{{ __('layout.language') }}"
            >
                <i class="ti ti-language"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 140px;">
                @foreach($availableLocales as $code => $label)
                    <li>
                        <a
                            href="{{ route('locale.switch', $code) }}"
                            @class(['dropdown-item', 'active' => $currentLocale === $code])
                            hreflang="{{ $code }}"
                        >
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (config('admin.show_notifications'))
        {{-- Notifications --}}
        <div class="dropdown">
            <button
                type="button"
                class="admin-navbar__action-btn"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="{{ __('layout.notifications') }}"
            >
                <i class="ti ti-bell"></i>
                <span class="badge-dot"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notifications-dropdown p-0">
                <div class="notifications-dropdown__header">
                    <h6>{{ __('layout.notifications') }}</h6>
                    <a href="#" class="text-primary text-decoration-none" style="font-size: 0.75rem;">{{ __('layout.mark_all_read') }}</a>
                </div>
                <div class="notifications-dropdown__list">
                    <a href="#" class="notifications-dropdown__item unread">
                        <div class="notifications-dropdown__item-icon" style="background: rgba(15,118,110,0.1); color: #0F766E;">
                            <i class="ti ti-flag"></i>
                        </div>
                        <div class="notifications-dropdown__item-content">
                            <div class="title">{{ __('layout.notifications_items.campaign_created', ['name' => 'Hope Jordan']) }}</div>
                            <div class="time">{{ __('layout.time.minutes_ago', ['count' => 5]) }}</div>
                        </div>
                    </a>
                    <a href="#" class="notifications-dropdown__item unread">
                        <div class="notifications-dropdown__item-icon" style="background: rgba(34,197,94,0.1); color: #22C55E;">
                            <i class="ti ti-user-check"></i>
                        </div>
                        <div class="notifications-dropdown__item-content">
                            <div class="title">{{ __('layout.notifications_items.staff_checkin') }}</div>
                            <div class="time">{{ __('layout.time.minutes_ago', ['count' => 32]) }}</div>
                        </div>
                    </a>
                    <a href="#" class="notifications-dropdown__item">
                        <div class="notifications-dropdown__item-icon" style="background: rgba(245,158,11,0.1); color: #F59E0B;">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <div class="notifications-dropdown__item-content">
                            <div class="title">{{ __('layout.notifications_items.patients_pending', ['count' => 12]) }}</div>
                            <div class="time">{{ __('layout.time.hours_ago', ['count' => 2]) }}</div>
                        </div>
                    </a>
                </div>
                <div class="notifications-dropdown__footer">
                    <a href="#">{{ __('layout.view_all_notifications') }}</a>
                </div>
            </div>
        </div>
        @endif

        {{-- User Dropdown --}}
        <div class="dropdown">
            <button
                type="button"
                class="admin-navbar__user"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <div class="admin-navbar__user-avatar">
                    <x-user-avatar :user="auth()->user()" size="sm" />
                </div>
                <div class="admin-navbar__user-info">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="role">{{ auth()->user()->primaryRoleLabel() }}</div>
                </div>
                <i class="ti ti-chevron-down text-muted" style="font-size: 0.875rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 200px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold" style="font-size: 0.875rem;">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                </li>
                @can('view', auth()->user())
                    <li><a class="dropdown-item" href="{{ route('administration.users.show', auth()->user()) }}"><i class="ti ti-user me-2"></i>{{ __('layout.profile') }}</a></li>
                @endcan
                @can('settings.view')
                    <li><a class="dropdown-item" href="{{ route('settings.dashboard') }}"><i class="ti ti-settings me-2"></i>{{ __('layout.settings') }}</a></li>
                @endcan
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="ti ti-logout me-2"></i>{{ __('layout.sign_out') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
