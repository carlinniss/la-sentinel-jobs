@php
    $placement = trim((string) ($placement ?? 'leaderboard'));
    $slotPath = trim((string) config('advertising.google_publisher.slots.leaderboard', '/17020487/LeaderBoard'));
    $isEnabled = (bool) config('advertising.google_publisher.enabled', true) && $slotPath !== '';
    $adId = 'div-gpt-ad-'.\Illuminate\Support\Str::slug($placement, '-');
@endphp

@if($isEnabled)
<aside class="google-publisher-leaderboard" aria-label="Advertisement" data-ad-placement="{{ $placement }}">
    <p class="mb-2 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Advertisement</p>
    <div class="mx-auto min-h-[50px] w-[320px] max-w-full overflow-hidden md:min-h-[90px] md:w-[728px]" id="{{ $adId }}">
        <script>
            window.googletag = window.googletag || { cmd: [] };
            window.lasentinelGoogleAds = window.lasentinelGoogleAds || { servicesEnabled: false, slots: {} };
            window.googletag.cmd.push(function () {
                var adId = @json($adId);
                var slotPath = @json($slotPath);

                if (! window.lasentinelGoogleAds.slots[adId]) {
                    var mapping = window.googletag.sizeMapping()
                        .addSize([768, 0], [728, 90])
                        .addSize([0, 0], [320, 50])
                        .build();
                    var slot = window.googletag.defineSlot(slotPath, [[728, 90], [320, 50]], adId);

                    if (slot) {
                        slot.defineSizeMapping(mapping).addService(window.googletag.pubads());
                        window.lasentinelGoogleAds.slots[adId] = true;
                    }
                }

                if (! window.lasentinelGoogleAds.servicesEnabled) {
                    window.googletag.pubads().enableSingleRequest();
                    window.googletag.enableServices();
                    window.lasentinelGoogleAds.servicesEnabled = true;
                }

                window.googletag.display(adId);
            });
        </script>
    </div>
</aside>
@endif
