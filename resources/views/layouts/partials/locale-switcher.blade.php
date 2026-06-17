<div class="locale-switcher {{ $class ?? '' }}">
    @foreach($availableLocales as $code => $label)
        <a
            href="{{ route('locale.switch', $code) }}"
            @class([
                'locale-switcher__btn',
                'active' => $currentLocale === $code,
            ])
            hreflang="{{ $code }}"
        >
            {{ $label }}
        </a>
    @endforeach
</div>
