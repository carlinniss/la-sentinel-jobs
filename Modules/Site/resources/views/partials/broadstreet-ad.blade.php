@php
    $zoneKey = trim((string) ($zone ?? ''));
    $requestedFormat = $format ?? 'inline';
    $format = in_array($requestedFormat, ['billboard', 'inline', 'cube'], true) ? $requestedFormat : 'inline';
    $placement = trim((string) ($placement ?? $zoneKey));
    $zoneId = trim((string) config("advertising.broadstreet.zones.{$zoneKey}", ''));
    $isLiveZone = (bool) config('advertising.broadstreet.enabled') && $zoneId !== '';
    $preview = (bool) config('advertising.broadstreet.preview', false);

    $formatClasses = match ($format) {
        'billboard' => 'mx-auto min-h-[100px] w-full max-w-[970px] md:min-h-[180px] lg:min-h-[250px]',
        'cube' => 'mx-auto min-h-[250px] w-full max-w-[360px]',
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
