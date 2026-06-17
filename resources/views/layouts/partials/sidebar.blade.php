<aside class="admin-sidebar" id="adminSidebar">
    <a href="{{ route('dashboard') }}" class="admin-sidebar__brand">
        <img
            src="{{ asset('images/ksrelief-logo-horizontal.png') }}"
            alt="{{ __('layout.brand_alt') }}"
            class="admin-sidebar__brand-image"
            width="300"
            height="99"
        >
        <img
            src="{{ asset('images/ksrelief-logo-icon.png') }}"
            alt="{{ __('layout.brand_alt') }}"
            class="admin-sidebar__brand-icon"
            width="36"
            height="36"
        >
    </a>

    <nav class="admin-sidebar__nav">
        @foreach($adminMenu as $item)
            <div class="admin-sidebar__group">
                @if(isset($item['children']) && $item['key'] === 'administration')
                    <div class="admin-sidebar__section">{{ $item['label'] }}</div>
                    @foreach($item['children'] as $child)
                        <a
                            href="{{ route($child['route']) }}"
                            class="admin-sidebar__link {{ request()->routeIs($child['route']) ? 'active' : '' }}"
                            title="{{ $child['label'] }}"
                        >
                            <i class="{{ $child['key'] === 'users' ? 'ti ti-users' : 'ti ti-shield-lock' }}"></i>
                            <span class="admin-sidebar__label">{{ $child['label'] }}</span>
                        </a>
                    @endforeach
                @elseif(isset($item['children']))
                    @php
                        $childRoutes = collect($item['children'])->pluck('route')->toArray();
                        $isGroupActive = collect($childRoutes)->contains(fn ($route) => request()->routeIs($route));
                    @endphp
                    <button
                        type="button"
                        class="admin-sidebar__collapse-toggle"
                        data-sidebar-collapse="#submenu-{{ $item['key'] }}"
                        aria-expanded="{{ $isGroupActive ? 'true' : 'false' }}"
                        title="{{ $item['label'] }}"
                    >
                        <i class="{{ $item['icon'] }}"></i>
                        <span class="admin-sidebar__label">{{ $item['label'] }}</span>
                        <i class="ti ti-chevron-down chevron ms-auto"></i>
                    </button>
                    <div
                        id="submenu-{{ $item['key'] }}"
                        class="admin-sidebar__submenu {{ $isGroupActive ? '' : 'd-none' }}"
                    >
                        @foreach($item['children'] as $child)
                            <a
                                href="{{ route($child['route']) }}"
                                class="admin-sidebar__link admin-sidebar__link--child {{ request()->routeIs($child['route']) ? 'active' : '' }}"
                            >
                                <span class="admin-sidebar__label">{{ $child['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <a
                        href="{{ route($item['route']) }}"
                        class="admin-sidebar__link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                        title="{{ $item['label'] }}"
                    >
                        <i class="{{ $item['icon'] }}"></i>
                        <span class="admin-sidebar__label">{{ $item['label'] }}</span>
                    </a>
                @endif
            </div>
        @endforeach
    </nav>

    <div class="admin-sidebar__footer">
        <small class="text-muted d-block text-center">&copy; {{ date('Y') }} {{ $adminName }}</small>
    </div>
</aside>
