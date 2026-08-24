@php
    $requestedFormat = trim((string) ($format ?? 'display'));
    $format = in_array($requestedFormat, ['display', 'leaderboard', 'skyscraper', 'half-page'], true) ? $requestedFormat : 'display';
    $placement = trim((string) ($placement ?? $format));
    $label = trim((string) ($label ?? 'Google ad placeholder'));
    $desktopSize = match ($format) {
        'leaderboard' => '728 x 90',
        'skyscraper' => '160 x 600',
        'half-page' => '300 x 600',
        default => '300 x 250',
    };
    $mobileSize = $format === 'leaderboard' ? '320 x 50' : $desktopSize;
@endphp

<aside
    class="google-ad-placeholder google-ad-placeholder-{{ $format }}"
    aria-label="Advertisement placeholder"
    data-ad-provider="google"
    data-ad-placement="{{ $placement }}"
    data-ad-format="{{ $format }}"
>
    <p>Advertisement</p>
    <div class="google-ad-placeholder-frame">
        <span class="google-ad-placeholder-size google-ad-placeholder-size-desktop">{{ $desktopSize }}</span>
        <span class="google-ad-placeholder-size google-ad-placeholder-size-mobile">{{ $mobileSize }}</span>
        <strong>{{ $label }}</strong>
    </div>
</aside>
