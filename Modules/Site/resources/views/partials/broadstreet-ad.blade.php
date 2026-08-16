@php
    $zoneKey = trim((string) ($zone ?? ''));
    $requestedFormat = $format ?? 'inline';
    $format = in_array($requestedFormat, ['billboard', 'inline', 'cube'], true) ? $requestedFormat : 'inline';
    $placement = trim((string) ($placement ?? $zoneKey));
    $zoneId = trim((string) config("advertising.broadstreet.zones.{$zoneKey}", ''));
    $isLiveZone = (bool) config('advertising.broadstreet.enabled') && $zoneId !== '';
    $showPlaceholder = (bool) config('advertising.broadstreet.show_placeholders', true);
    $preview = (bool) config('advertising.broadstreet.preview', false);

    $formatClasses = match ($format) {
        'billboard' => 'mx-auto min-h-[100px] w-full max-w-[970px] md:min-h-[180px] lg:min-h-[250px]',
        'cube' => 'mx-auto min-h-[250px] w-full max-w-[320px]',
        default => 'mx-auto min-h-[100px] w-full max-w-[970px] md:min-h-[140px]',
    };

    $formatLabel = match ($format) {
        'billboard' => 'Billboard · 970×250 / 320×100 mobile',
        'cube' => 'Amazing Cube · 300×250',
        default => 'In-feed display placement',
    };
@endphp

@if($isLiveZone || $showPlaceholder)
<aside
    class="broadstreet-ad-slot"
    aria-label="Advertisement"
    data-ad-placement="{{ $placement }}"
    data-ad-format="{{ $format }}"
>
    <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Advertisement</p>
    <div class="{{ $formatClasses }} overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($isLiveZone)
            <broadstreet-zone
                zone-id="{{ $zoneId }}"
                uri-keywords="true"
                soft-keywords="true"
                @if($preview) preview="true" @endif
                style="display:block; min-height:100%; width:100%;"
            ></broadstreet-zone>
        @else
            <div class="flex min-h-[inherit] h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-white to-amber-50 px-5 py-8 text-center">
                <span class="text-xs font-black uppercase tracking-[0.16em] text-[#8b1d22]">LA Sentinel Jobs</span>
                <span class="mt-2 text-sm font-bold text-slate-700">Advertising space</span>
                <span class="mt-1 text-xs text-slate-400">{{ $formatLabel }}</span>
            </div>
        @endif
    </div>
</aside>
@endif
