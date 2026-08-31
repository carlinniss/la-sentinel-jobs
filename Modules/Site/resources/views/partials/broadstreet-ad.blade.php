@php
    $zoneKey = trim((string) ($zone ?? ''));
    $requestedFormat = $format ?? 'inline';
    $format = in_array($requestedFormat, ['billboard', 'leaderboard', 'inline', 'display', 'cube', 'skyscraper', 'half-page'], true) ? $requestedFormat : 'inline';
    $placement = trim((string) ($placement ?? $zoneKey));
    $zoneId = trim((string) config("advertising.broadstreet.zones.{$zoneKey}", ''));
    $isLiveZone = (bool) config('advertising.broadstreet.enabled') && $zoneId !== '';
    $preview = (bool) config('advertising.broadstreet.preview', false);

    $formatClasses = match ($format) {
        'billboard' => 'mx-auto min-h-[100px] w-full max-w-[970px] md:min-h-[180px] lg:min-h-[250px]',
        'leaderboard' => 'mx-auto min-h-[50px] w-[320px] max-w-full md:min-h-[90px] md:w-[728px]',
        'display' => 'mx-auto min-h-[250px] w-full max-w-[300px]',
        'cube' => 'mx-auto min-h-[250px] w-full max-w-[360px]',
        'skyscraper' => 'mx-auto min-h-[600px] w-full max-w-[160px]',
        'half-page' => 'mx-auto min-h-[600px] w-full max-w-[300px]',
        default => 'mx-auto min-h-[100px] w-full max-w-[970px] md:min-h-[140px]',
    };
@endphp

@if($isLiveZone)
<aside
    class="broadstreet-ad-slot"
    aria-label="Advertisement"
    data-ad-placement="{{ $placement }}"
    data-ad-format="{{ $format }}"
>
    <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Advertisement</p>
    <div class="{{ $formatClasses }} flex items-center justify-center overflow-hidden">
        <broadstreet-zone
            class="block h-full min-h-[inherit] w-full"
            zone-id="{{ $zoneId }}"
            uri-keywords="true"
            soft-keywords="true"
            @if($preview) preview="true" @endif
            style="min-height:inherit;"
        ></broadstreet-zone>
    </div>
</aside>
@endif
