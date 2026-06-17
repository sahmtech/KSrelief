<div class="auth-card__brand">
    <img
        src="{{ asset('images/ksrelief-logo-horizontal.png') }}"
        alt="{{ __('layout.brand_alt') }}"
        class="auth-card__logo"
        width="300"
        height="99"
    >

    <p class="auth-card__welcome">{{ __('auth.welcome_back') }}</p>
    <p class="auth-card__description">{{ __('auth.brand_description') }}</p>

    <ul class="auth-card__features">
        <li class="auth-card__feature">
            <span class="auth-card__feature-icon"><i class="ti ti-flag"></i></span>
            <span>{{ __('auth.feature_campaigns') }}</span>
        </li>
        <li class="auth-card__feature">
            <span class="auth-card__feature-icon"><i class="ti ti-stethoscope"></i></span>
            <span>{{ __('auth.feature_staff') }}</span>
        </li>
        <li class="auth-card__feature">
            <span class="auth-card__feature-icon"><i class="ti ti-users"></i></span>
            <span>{{ __('auth.feature_patients') }}</span>
        </li>
    </ul>
</div>
