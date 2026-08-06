@extends('app::layouts.app')
@section('content')
@php
    $menuCategories = $categories->take(8);
    $spotlightListings = $featuredListings->isNotEmpty()
        ? $featuredListings->take(4)
        : $recentListings->take(4);
    $listingCards = $recentListings->take(6);
    $demoEnabled = (bool) config('demo.enabled');
    $prepareDemoRoute = $demoEnabled ? route('demo.prepare') : null;
    $prepareDemoRedirect = url()->full();
    $hasDemoSession = (bool) session('is_demo_session') || filled(session('demo_uuid'));
    $demoLandingMode = $demoEnabled && !auth()->check() && !$hasDemoSession;
    $demoTurnstileProtectionEnabled = (bool) config('demo.turnstile.enabled', false);
    $demoTurnstileSiteKey = trim((string) config('demo.turnstile.site_key', ''));
    $prepareDemoTurnstileRequired = $demoLandingMode && $demoTurnstileProtectionEnabled;
    $prepareDemoTurnstileRenderable = $prepareDemoTurnstileRequired && $demoTurnstileSiteKey !== '';
    $demoTtlMinutes = (int) config('demo.ttl_minutes', 360);
    $demoTtlHours = intdiv($demoTtlMinutes, 60);
    $demoTtlRemainderMinutes = $demoTtlMinutes % 60;
    $demoTtlLabelParts = [];

    if ($demoTtlHours > 0) {
        $demoTtlLabelParts[] = $demoTtlHours.' '.\Illuminate\Support\Str::plural('hour', $demoTtlHours);
    }

    if ($demoTtlRemainderMinutes > 0) {
        $demoTtlLabelParts[] = $demoTtlRemainderMinutes.' '.\Illuminate\Support\Str::plural('minute', $demoTtlRemainderMinutes);
    }

    $demoTtlLabel = $demoTtlLabelParts !== [] ? implode(' ', $demoTtlLabelParts) : '0 minutes';
    $homeSlide = collect($generalSettings['home_slides'] ?? [])
        ->first(fn ($slide): bool => is_array($slide));

    $heroBadge = trim((string) ($homeSlide['badge'] ?? '')) ?: 'LA Sentinel Jobs';
    $heroTitle = trim((string) ($homeSlide['title'] ?? '')) ?: 'Find local jobs and hiring opportunities across Los Angeles.';
    $heroSubtitle = trim((string) ($homeSlide['subtitle'] ?? '')) ?: 'A community jobs board for employers, applicants, and Sentinel readers.';
    $heroPrimaryLabel = trim((string) ($homeSlide['primary_button_text'] ?? '')) ?: 'Browse Jobs';
    $heroSecondaryLabel = trim((string) ($homeSlide['secondary_button_text'] ?? '')) ?: 'Post a Job';
    $sentinelUrl = 'https://lasentinel.net';
    $formatCompensation = static function ($listing): string {
        if (! $listing->price || (float) $listing->price <= 0) {
            return __('listing::messages.price_on_request');
        }

        $amount = (float) $listing->price;
        $prefix = ($listing->currency ?? 'USD') === 'USD' ? '$' : '';
        $suffix = $amount < 1000 ? '/hr' : '/yr';

        return $prefix.number_format($amount, 0).$suffix;
    };
@endphp

@if($demoLandingMode && $prepareDemoRoute)
<div class="min-h-screen flex items-center justify-center px-5 py-10">
    <form method="POST" action="{{ $prepareDemoRoute }}" data-demo-prepare-form data-turnstile-required="{{ $prepareDemoTurnstileRequired ? '1' : '0' }}" class="w-full max-w-xl rounded-[32px] border border-slate-200 bg-white p-8 md:p-10">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ $prepareDemoRedirect }}">
        <h1 class="text-3xl md:text-5xl font-extrabold text-slate-950">Prepare Demo</h1>
        <p class="mt-5 text-base md:text-lg leading-8 text-slate-600">
            Launch a private seeded jobs board for this browser. Jobs, favorites, inbox data, and admin access are prepared automatically.
        </p>
        <p class="mt-4 text-base text-slate-500">
            This demo is deleted automatically after {{ $demoTtlLabel }}.
        </p>
        @if($prepareDemoTurnstileRenderable)
        <div class="mt-6 space-y-2">
            <div class="cf-turnstile" data-sitekey="{{ $demoTurnstileSiteKey }}"></div>
            <p class="text-xs text-slate-500">Complete the security check before starting your private demo.</p>
        </div>
        @elseif($prepareDemoTurnstileRequired)
        <p class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium leading-6 text-amber-700">
            Security check is enabled but the widget is not configured. Contact the administrator.
        </p>
        @endif
        <p data-demo-prepare-status data-turnstile-message="Please complete the security verification first." data-loading-message="Preparing your private demo. This can take longer because a dedicated seeded environment is being provisioned for your browser." aria-live="polite" class="mt-4 hidden rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium leading-6 text-blue-800">
            Preparing your private demo. This can take longer because a dedicated seeded environment is being provisioned for your browser.
        </p>
        <button type="submit" data-demo-prepare-button @if($prepareDemoTurnstileRequired) disabled @endif class="mt-8 inline-flex min-h-16 w-full items-center justify-center rounded-full bg-blue-600 px-8 py-4 text-lg font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-500">
            <span data-demo-prepare-idle>Prepare Demo</span>
            <span data-demo-prepare-loading class="hidden items-center gap-2">
                Preparing Demo...
            </span>
        </button>
    </form>
</div>
@else
<div class="max-w-[1120px] mx-auto px-4 py-8 md:py-14 space-y-10 md:space-y-16">
    <section class="grid lg:grid-cols-[1.1fr,0.9fr] gap-8 lg:gap-12 items-center">
        <div>
            <p class="text-xs uppercase text-[var(--oc-muted)] font-semibold mb-3">{{ $heroBadge }}</p>
            <h1 class="text-3xl md:text-5xl leading-[1.1] font-semibold text-[var(--oc-text)] max-w-xl">{{ $heroTitle }}</h1>
            <p class="mt-4 text-[var(--oc-muted)] text-base md:text-lg max-w-xl leading-7">{{ $heroSubtitle }}</p>
            <a href="{{ $sentinelUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex items-center gap-3 rounded-2xl border border-[#d9c08a] bg-white px-4 py-3 shadow-sm hover:border-[#b99548]">
                <img src="{{ asset('images/la-sentinel/logo.webp') }}" alt="Los Angeles Sentinel" class="h-12 w-auto">
                <span class="text-left">
                    <span class="block text-sm font-extrabold text-[#7b1a1f]">Published with the Los Angeles Sentinel</span>
                    <span class="block text-xs font-semibold text-[var(--oc-muted)]">A Bakewell Media jobs platform for Sentinel readers.</span>
                </span>
                <span class="hidden rounded-full bg-black p-1 sm:inline-flex">
                    <img src="{{ asset('images/bakewell-media/logo.png') }}" alt="Bakewell Media" class="h-10 w-10 rounded-full object-contain">
                </span>
            </a>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('listings.index') }}" class="btn-primary px-6 py-3 text-sm font-semibold">
                    {{ $heroPrimaryLabel }}
                </a>
                @auth
                <a href="{{ route('panel.listings.create') }}" class="oc-text-link px-2 py-3 text-sm font-semibold">
                    {{ $heroSecondaryLabel }}
                </a>
                @else
                <a href="{{ route('login') }}" class="oc-text-link px-2 py-3 text-sm font-semibold">
                    {{ $heroSecondaryLabel }}
                </a>
                @endauth
            </div>
        </div>
        <div class="rounded-[28px] border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5 shadow-[0_18px_45px_rgba(29,29,31,0.08)]">
            <div class="flex items-center justify-between gap-4 border-b border-[var(--oc-border)] pb-4">
                <div>
                    <p class="text-xs uppercase text-[#8b1d22] font-bold">Now Hiring</p>
                    <h2 class="mt-1 text-2xl font-semibold text-[var(--oc-text)]">LA Sentinel Jobs Board</h2>
                    <p class="mt-1 text-sm font-semibold text-[var(--oc-muted)]">Published by the Los Angeles Sentinel and Bakewell Media</p>
                </div>
                <div class="rounded-2xl bg-[#8b1d22] px-4 py-3 text-right text-white">
                    <p class="text-2xl font-semibold leading-none">{{ number_format($listingCount ?? $spotlightListings->count()) }}</p>
                    <p class="mt-1 text-[11px] uppercase text-white/75">Open roles</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                @forelse($spotlightListings->take(3) as $spotlightListing)
                @php
                    $spotlightCategory = $spotlightListing->category?->parent?->name ?? $spotlightListing->category?->name ?? 'Local Role';
                    $spotlightLocation = trim(collect([$spotlightListing->city, $spotlightListing->country])->filter()->join(', '));
                    $spotlightEmployer = trim((string) ($spotlightListing->user?->name ?? ''));
                    $spotlightIsBmo = $spotlightEmployer !== '' && str_contains(strtolower($spotlightEmployer), 'bmo');
                @endphp
                <a href="{{ route('listings.show', $spotlightListing) }}" class="block border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                    @if($spotlightEmployer !== '')
                    <div class="mb-3 flex items-center gap-2">
                        @if($spotlightIsBmo)
                        @include('listing::partials.bmo-employer-badge', ['employerName' => $spotlightEmployer, 'employer' => $spotlightListing->user])
                        @else
                        <span class="truncate text-xs font-semibold text-[var(--oc-muted)]">{{ $spotlightEmployer }}</span>
                        @endif
                    </div>
                    @endif
                    <div class="flex items-center justify-between gap-3">
                        <span class="rounded-full bg-[#f5ead8] px-3 py-1 text-xs font-bold text-[#7b1a1f]">{{ $spotlightCategory }}</span>
                        <span class="text-sm font-semibold text-[var(--oc-text)]">{{ $formatCompensation($spotlightListing) }}</span>
                    </div>
                    <h3 class="mt-3 text-lg font-semibold text-[var(--oc-text)]">{{ $spotlightListing->title }}</h3>
                    <p class="mt-1 text-sm text-[var(--oc-muted)]">{{ $spotlightLocation !== '' ? $spotlightLocation : 'Los Angeles' }}</p>
                </a>
                @empty
                <div class="space-y-4">
                    <div class="border-b border-slate-100 pb-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="rounded-full bg-[#f5ead8] px-3 py-1 text-xs font-bold text-[#7b1a1f]">Healthcare</span>
                            <span class="text-sm font-semibold text-[var(--oc-text)]">$28/hr</span>
                        </div>
                        <h3 class="mt-3 text-lg font-semibold text-[var(--oc-text)]">Community Clinic Intake Coordinator</h3>
                        <p class="mt-1 text-sm text-[var(--oc-muted)]">Crenshaw, United States</p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="rounded-full bg-[#f5ead8] px-3 py-1 text-xs font-bold text-[#7b1a1f]">Education</span>
                            <span class="text-sm font-semibold text-[var(--oc-text)]">$52,000/yr</span>
                        </div>
                        <h3 class="mt-3 text-lg font-semibold text-[var(--oc-text)]">After-School Literacy Instructor</h3>
                        <p class="mt-1 text-sm text-[var(--oc-muted)]">Leimert Park, United States</p>
                    </div>
                </div>
                @endforelse
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-4">
                    <p class="text-xl font-semibold text-[#8b1d22]">Local</p>
                    <p class="mt-1 text-xs font-medium text-[var(--oc-muted)]">employers</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-4">
                    <p class="text-xl font-semibold text-[#8b1d22]">Career</p>
                    <p class="mt-1 text-xs font-medium text-[var(--oc-muted)]">resources</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-4">
                    <p class="text-xl font-semibold text-[#8b1d22]">Fast</p>
                    <p class="mt-1 text-xs font-medium text-[var(--oc-muted)]">messaging</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <article class="rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="text-xs uppercase text-[#8b1d22] font-bold">Jobs</p>
            <h2 class="mt-3 text-xl font-semibold text-[var(--oc-text)]">Fresh local listings</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">Roles across healthcare, education, trades, media, hospitality, public service, and technology.</p>
        </article>
        <article class="rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="text-xs uppercase text-[#8b1d22] font-bold">Employers</p>
            <h2 class="mt-3 text-xl font-semibold text-[var(--oc-text)]">Profiles that build trust</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">Hiring teams can show mission, pay, benefits, location, and next steps before applicants apply.</p>
        </article>
        <article class="rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="text-xs uppercase text-[#8b1d22] font-bold">Resources</p>
            <h2 class="mt-3 text-xl font-semibold text-[var(--oc-text)]">Career support</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">A natural home for resume help, hiring events, training pathways, and community workforce programs.</p>
        </article>
        <article class="rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="text-xs uppercase text-[#8b1d22] font-bold">Wellness</p>
            <h2 class="mt-3 text-xl font-semibold text-[var(--oc-text)]">Better work, better future</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">A demo-ready path from finding work to building stability for families and communities.</p>
        </article>
    </section>

    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl md:text-2xl font-semibold text-[var(--oc-text)]">{{ __('site::messages.browse_categories') }}</h2>
            <a href="{{ route('categories.index') }}" class="oc-text-link text-sm font-semibold">
                {{ __('site::messages.view_all') }}
            </a>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($menuCategories as $category)
            @php
                $categoryIconUrl = $category->iconUrl();
                $fallbackLabel = strtoupper(\Illuminate\Support\Str::substr($category->name, 0, 1));
            @endphp
            <a href="{{ route('listings.index', ['category' => $category->id]) }}" class="oc-pill">
                @if($categoryIconUrl)
                    <img src="{{ $categoryIconUrl }}" alt="" class="h-4 w-4 object-contain">
                @else
                    <span class="text-xs font-semibold">{{ $fallbackLabel }}</span>
                @endif
                <span>{{ $category->name }}</span>
            </a>
            @endforeach
        </div>
    </section>

    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl md:text-2xl font-semibold text-[var(--oc-text)]">{{ __('site::messages.recent_listings') }}</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($listingCards as $listing)
            @php
                $listingImage = $listing->primaryImageData('card');
                $priceLabel = $formatCompensation($listing);
                $locationLabel = trim(collect([$listing->city, $listing->country])->filter()->join(', '));
                $employerName = trim((string) ($listing->user?->name ?? ''));
                $isBmoEmployer = $employerName !== '' && str_contains(strtolower($employerName), 'bmo');
                $isFavorited = in_array($listing->id, $favoriteListingIds ?? [], true);
            @endphp
            <article class="rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] overflow-hidden">
                <div class="relative h-56 md:h-64 bg-[var(--oc-bg)]">
                    <a href="{{ route('listings.show', $listing) }}" class="block h-full w-full" aria-label="{{ $listing->title }}">
                        @if($listingImage)
                        @include('listing::partials.responsive-image', [
                            'image' => $listingImage,
                            'alt' => $listing->title,
                            'class' => 'w-full h-full object-cover',
                        ])
                        @else
                        <div class="w-full h-full grid place-items-center text-[var(--oc-muted)] text-sm">
                            No image
                        </div>
                        @endif
                    </a>
                    @if($listing->is_featured)
                    <span class="absolute top-3 left-3 bg-[var(--oc-surface)] border border-[var(--oc-border)] text-[var(--oc-text)] text-xs font-semibold px-2 py-1 rounded-full">Featured</span>
                    @endif
                    <div class="absolute top-3 right-3">
                        @auth
                        <form method="POST" action="{{ route('favorites.listings.toggle', $listing) }}">
                            @csrf
                            <button type="submit" class="w-9 h-9 rounded-full grid place-items-center border border-[var(--oc-border)] {{ $isFavorited ? 'bg-[var(--oc-text)] text-white' : 'bg-[var(--oc-surface)] text-[var(--oc-muted)]' }}">♥</button>
                        </form>
                        @else
                        <a href="{{ route('login') }}" class="w-9 h-9 rounded-full bg-[var(--oc-surface)] border border-[var(--oc-border)] text-[var(--oc-muted)] grid place-items-center">♡</a>
                        @endauth
                    </div>
                </div>
                <div class="p-4">
                    @if($employerName !== '')
                    <div class="mb-3 flex items-center gap-2">
                        @if($isBmoEmployer)
                        @include('listing::partials.bmo-employer-badge', ['employerName' => $employerName, 'employer' => $listing->user])
                        @else
                        <span class="truncate text-xs font-semibold text-[var(--oc-muted)]">{{ $employerName }}</span>
                        @endif
                    </div>
                    @endif
                    <p class="text-xl font-semibold text-[var(--oc-text)]">{{ $priceLabel }}</p>
                    <h3 class="text-sm font-medium text-[var(--oc-text)] mt-1 truncate">{{ $listing->title }}</h3>
                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-[var(--oc-muted)]">
                        <span class="truncate">{{ $locationLabel !== '' ? $locationLabel : 'Location not specified' }}</span>
                        <span class="shrink-0">{{ $listing->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </article>
            @empty
            <div class="col-span-2 border border-dashed border-[var(--oc-border)] rounded-2xl py-20 text-center text-[var(--oc-muted)]">
                {{ __('listing::messages.no_listings_yet') }}
            </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-[var(--oc-border)] px-6 py-8 md:px-10 md:py-12">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-semibold text-[var(--oc-text)]">{{ __('site::messages.sell_something') }}</h2>
                <p class="text-[var(--oc-muted)] mt-2 text-sm md:text-base">Post a local job in minutes and reach LA Sentinel readers who are ready for the next step.</p>
            </div>
            @auth
            <a href="{{ route('panel.listings.create') }}" class="btn-primary px-6 py-3 font-semibold whitespace-nowrap">
                {{ __('site::messages.post_listing_cta') }}
            </a>
            @else
            <a href="{{ route('register') }}" class="btn-primary px-6 py-3 font-semibold whitespace-nowrap">
                {{ __('site::messages.start_free') }}
            </a>
            @endauth
        </div>
    </section>
</div>
@endif
@if($prepareDemoTurnstileRenderable)
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
@endsection
