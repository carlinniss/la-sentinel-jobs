@extends('app::layouts.app')
@section('content')
@php
    $menuCategories = $categories->take(8);
    $spotlightListings = $featuredListings->isNotEmpty()
        ? $featuredListings->take(4)
        : $recentListings->take(4);
    $listingCards = $recentListings->take(6);
    $featuredEmployerCards = collect($featuredEmployers ?? []);
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

    $heroBadge = trim((string) ($homeSlide['badge'] ?? '')) ?: 'LA Sentinel Community Jobs';
    $heroTitle = trim((string) ($homeSlide['title'] ?? '')) ?: 'Jobs, hiring events, and career pathways for Black Los Angeles.';
    $heroSubtitle = trim((string) ($homeSlide['subtitle'] ?? '')) ?: 'A trusted community jobs platform connecting Sentinel readers with employers, training partners, and neighborhood opportunities.';
    $heroPrimaryLabel = trim((string) ($homeSlide['primary_button_text'] ?? '')) ?: 'Browse Jobs';
    $heroSecondaryLabel = trim((string) ($homeSlide['secondary_button_text'] ?? '')) ?: 'Post a Job';
    $legacyHeroTitles = [
        'Find local jobs and hiring opportunities across Los Angeles.',
        'Connect Los Angeles talent with community-centered employers.',
    ];
    $legacyHeroSubtitles = [
        'A community jobs board for employers, applicants, and Sentinel readers.',
        'A focused jobs board for Sentinel readers, local businesses, nonprofits, schools, healthcare teams, and public agencies.',
    ];

    if (in_array($heroTitle, $legacyHeroTitles, true)) {
        $heroTitle = 'Jobs, hiring events, and career pathways for Black Los Angeles.';
    }

    if (in_array($heroSubtitle, $legacyHeroSubtitles, true)) {
        $heroSubtitle = 'A trusted community jobs platform connecting Sentinel readers with employers, training partners, and neighborhood opportunities.';
    }

    if ($heroBadge === 'LA Sentinel Jobs') {
        $heroBadge = 'LA Sentinel Community Jobs';
    }

    $sentinelUrl = 'https://lasentinel.net';
    $jobSearchRoute = route('listings.index');
    $postJobRoute = auth()->check() ? route('panel.listings.create') : route('login');
    $partnerRoute = route('partners.inquiry');
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
<div class="community-home-shell max-w-[1120px] mx-auto px-4 py-8 md:py-14 space-y-10 md:space-y-16">
    <section class="community-action-strip community-action-strip-loud">
        <div class="min-w-0">
            <p class="community-kicker">Start Here</p>
            <h2 class="mt-2 text-2xl font-black text-slate-950 md:text-3xl">Choose the path that fits you.</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 md:text-base">LA Sentinel Jobs is built for people looking for work, employers ready to hire, and partners bringing career pathways to the community.</p>
            <div class="mt-5 grid gap-3 text-left md:grid-cols-3">
                <a href="{{ $jobSearchRoute }}" class="community-mini-step" aria-label="Search local jobs">
                    <strong>Search jobs</strong>
                    <p>Browse local roles, apply faster, and keep your resume ready for hiring events.</p>
                </a>
                <a href="{{ $postJobRoute }}" class="community-mini-step" aria-label="Post jobs for LA Sentinel candidates">
                    <strong>Post jobs</strong>
                    <p>Share open roles and reach candidates connected to the LA Sentinel community.</p>
                </a>
                <a href="{{ $partnerRoute }}" class="community-mini-step" aria-label="Partner with LA Sentinel Jobs">
                    <strong>Partner with us</strong>
                    <p>Bring hiring events, training programs, and workforce pathways to the LA Sentinel team.</p>
                </a>
            </div>
        </div>
        <div class="community-action-buttons">
            <a href="{{ $jobSearchRoute }}" class="btn-primary community-action-primary community-action-card justify-center px-6 py-3 text-sm font-black" style="--action-image: url('{{ asset('images/la-sentinel/cta-apply-jobs.png') }}');">
                <span>Search Jobs</span>
            </a>
            <a href="{{ $postJobRoute }}" class="community-secondary-button community-action-card" style="--action-image: url('{{ asset('images/la-sentinel/cta-post-job.png') }}');">
                <span>Post Jobs</span>
            </a>
            <a href="{{ $partnerRoute }}" class="community-secondary-button community-action-card" style="--action-image: url('{{ asset('images/la-sentinel/cta-post-resume.png') }}');">
                <span>Partner With Us</span>
            </a>
        </div>
    </section>

    @include('site::partials.google-ad-placeholder', [
        'format' => 'leaderboard',
        'placement' => 'home-top-leaderboard',
        'label' => 'Top jobs sponsor ad',
    ])

    <section class="community-hero-grid grid lg:grid-cols-[1.1fr,0.9fr] gap-8 lg:gap-12 items-start">
        <div>
            <p class="community-kicker mb-3">{{ $heroBadge }}</p>
            <h1 class="max-w-xl text-3xl font-black leading-[1.05] text-[var(--oc-text)] md:text-5xl">{{ $heroTitle }}</h1>
            <p class="mt-4 max-w-xl text-base leading-7 text-[var(--oc-muted)] md:text-lg">{{ $heroSubtitle }}</p>
            <div class="mt-5 flex flex-wrap gap-2">
                <span class="community-proof-pill">Hiring fairs</span>
                <span class="community-proof-pill">Career training</span>
                <span class="community-proof-pill">Local employers</span>
            </div>
            <a href="{{ $sentinelUrl }}" target="_blank" rel="noopener" class="community-brand-card mt-6 inline-flex items-center gap-3">
                <img src="{{ asset('images/la-sentinel/logo.webp') }}" alt="Los Angeles Sentinel" class="h-12 w-auto">
                <span class="text-left">
                    <span class="block text-sm font-extrabold text-[#7b1a1f]">Published with the Los Angeles Sentinel</span>
                    <span class="block text-xs font-semibold text-[var(--oc-muted)]">Built for community hiring, outreach, and workforce access.</span>
                </span>
                <span class="hidden items-center gap-2 rounded-full bg-black py-1 pl-1 pr-3 text-xs font-bold text-white sm:inline-flex">
                    <img src="{{ asset('images/bakewell-media/logo.png') }}" alt="Bakewell Media" class="h-10 w-10 rounded-full object-contain">
                    Bakewell Media
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
            <div class="mt-10 max-w-[420px]">
                @include('site::partials.broadstreet-ad', [
                    'zone' => 'cube',
                    'format' => 'cube',
                    'placement' => 'home-hero-left',
                ])
            </div>
        </div>
        <div class="community-jobs-panel rounded-[28px] border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5 shadow-[0_18px_45px_rgba(29,29,31,0.08)]">
            <div class="community-hero-photo mb-5">
                <img src="{{ asset('images/la-sentinel/community-jobs-hero-alt.png') }}" alt="Los Angeles community workforce professionals">
                <span>Los Angeles workforce pathways</span>
            </div>

            <div class="flex items-center justify-between gap-4 border-b border-[var(--oc-border)] pb-4">
                <div>
                    <p class="community-kicker">Community Hiring</p>
                    <h2 class="mt-1 text-2xl font-black text-[var(--oc-text)]">LA Sentinel Jobs Board</h2>
                    <p class="mt-1 text-sm font-semibold text-[var(--oc-muted)]">Jobs, outreach, and pathways for Black Los Angeles.</p>
                </div>
                <div class="rounded-2xl bg-[#14110f] px-4 py-3 text-right text-white ring-2 ring-[#d6a641]">
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
                    $spotlightBrand = \Modules\User\App\Support\FeaturedEmployerBrand::forName($spotlightEmployer);
                @endphp
                <a href="{{ route('listings.show', $spotlightListing) }}" class="block border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                    @if($spotlightEmployer !== '')
                    <div class="mb-3 flex items-center gap-2">
                        @if($spotlightBrand)
                        @include('listing::partials.featured-employer-badge', ['employerName' => $spotlightEmployer, 'employer' => $spotlightListing->user])
                        @else
                        <span class="truncate text-xs font-semibold text-[var(--oc-muted)]">{{ $spotlightEmployer }}</span>
                        @endif
                    </div>
                    @endif
                    <div class="flex items-center justify-between gap-3">
                        <span class="community-proof-pill">{{ $spotlightCategory }}</span>
                        <span class="text-sm font-semibold text-[var(--oc-text)]">{{ $formatCompensation($spotlightListing) }}</span>
                    </div>
                    <h3 class="mt-3 text-lg font-semibold text-[var(--oc-text)]">{{ $spotlightListing->title }}</h3>
                    <p class="mt-1 text-sm text-[var(--oc-muted)]">{{ $spotlightLocation !== '' ? $spotlightLocation : 'Los Angeles' }}</p>
                </a>
                @empty
                <div class="space-y-4">
                    <div class="border-b border-slate-100 pb-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="community-proof-pill">Healthcare</span>
                            <span class="text-sm font-semibold text-[var(--oc-text)]">$28/hr</span>
                        </div>
                        <h3 class="mt-3 text-lg font-semibold text-[var(--oc-text)]">Community Clinic Intake Coordinator</h3>
                        <p class="mt-1 text-sm text-[var(--oc-muted)]">Crenshaw, United States</p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="community-proof-pill">Education</span>
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
                    <p class="text-xl font-black text-[#8b1d22]">Local</p>
                    <p class="mt-1 text-xs font-bold text-[var(--oc-muted)]">employers</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-4">
                    <p class="text-xl font-black text-[#8b1d22]">Career</p>
                    <p class="mt-1 text-xs font-bold text-[var(--oc-muted)]">pathways</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-4">
                    <p class="text-xl font-black text-[#8b1d22]">Trusted</p>
                    <p class="mt-1 text-xs font-bold text-[var(--oc-muted)]">outreach</p>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-employers-section">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="text-left">
                <p class="community-kicker">Featured Employers</p>
                <h2 class="mt-2 text-2xl font-black text-[var(--oc-text)] md:text-3xl">Hiring partners investing in community careers</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--oc-muted)]">Featured employers appear higher on the jobs board, get dedicated profiles, and give LA Sentinel a clearer way to show applicant attribution.</p>
            </div>
            <a href="{{ route('listings.index') }}" class="oc-text-link text-left text-sm font-bold md:text-right">Browse partner jobs</a>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.25fr),minmax(18rem,0.75fr)]">
            <div class="grid gap-4">
                @forelse($featuredEmployerCards->take(3) as $featuredEmployer)
                @php
                    $featuredEmployerName = trim((string) $featuredEmployer->name);
                    $featuredEmployerBio = trim((string) ($featuredEmployer->profile?->bio ?? ''));
                    $featuredEmployerWebsite = trim((string) ($featuredEmployer->profile?->website ?? ''));
                    $featuredEmployerBrand = \Modules\User\App\Support\FeaturedEmployerBrand::forName($featuredEmployerName);
                    $isLeadFeaturedEmployer = ($featuredEmployerBrand['key'] ?? null) === 'st-johns';
                @endphp
                <article @class([
                    'featured-employer-card',
                    'featured-employer-card-lead' => $isLeadFeaturedEmployer,
                ]) @if($isLeadFeaturedEmployer) style="border-color: {{ $featuredEmployerBrand['soft_border'] }}; box-shadow: 0 22px 50px rgba(188, 66, 47, 0.14);" @endif>
                    <div class="featured-employer-logo-panel" @if($featuredEmployerBrand) style="background: {{ $featuredEmployerBrand['panel_background'] }}; border-color: {{ $featuredEmployerBrand['soft_border'] }};" @endif>
                        @if($featuredEmployerBrand)
                            <img src="{{ asset($featuredEmployerBrand['logo']) }}" alt="{{ $featuredEmployerBrand['logo_alt'] }}" @class([
                                'w-auto max-w-full object-contain',
                                'h-28 md:h-36' => $isLeadFeaturedEmployer,
                                'h-24 md:h-28' => ! $isLeadFeaturedEmployer,
                            ])>
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-950 text-3xl font-black text-white">
                                {{ mb_strtoupper(mb_substr($featuredEmployerName, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 text-left">
                        <p class="text-xs font-black uppercase tracking-[0.22em]" style="color: {{ $featuredEmployerBrand['primary'] ?? '#005eb8' }};">Featured employer partner</p>
                        <h3 class="mt-2 text-2xl font-black text-slate-950">{{ $featuredEmployerName }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $featuredEmployerBio !== '' ? \Illuminate\Support\Str::limit($featuredEmployerBio, 220) : 'This featured employer is hiring through LA Sentinel Jobs with roles connected to local workforce pathways.' }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full px-3 py-1.5 text-xs font-black" style="background: {{ $featuredEmployerBrand['soft'] ?? '#eef6ff' }}; color: {{ $featuredEmployerBrand['primary'] ?? '#005eb8' }}; box-shadow: 0 0 0 1px {{ $featuredEmployerBrand['soft_border'] ?? '#b9d8f2' }};">{{ number_format((int) $featuredEmployer->active_listings_count) }} featured jobs</span>
                            <span class="community-proof-pill">Career pathways</span>
                            <span class="community-proof-pill">Applicant attribution</span>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('employers.show', $featuredEmployer) }}" class="btn-primary inline-flex min-h-11 items-center px-5 text-sm font-black">View profile</a>
                            <a href="{{ route('listings.index', ['user' => $featuredEmployer->getKey()]) }}" class="inline-flex min-h-11 items-center rounded-full border border-slate-300 bg-white px-5 text-sm font-black text-slate-800 hover:border-[#005eb8]">View jobs</a>
                            @if($featuredEmployerWebsite !== '')
                                <a href="{{ $featuredEmployerWebsite }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center rounded-full px-5 text-sm font-black" style="border: 1px solid {{ $featuredEmployerBrand['soft_border'] ?? '#b9d8f2' }}; background: {{ $featuredEmployerBrand['soft'] ?? '#eef6ff' }}; color: {{ $featuredEmployerBrand['primary'] ?? '#005eb8' }};">Careers site</a>
                            @endif
                        </div>
                    </div>
                </article>
                @empty
                <article class="featured-employer-card">
                    <div class="featured-employer-logo-panel">
                        <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-24 w-auto max-w-full md:h-28">
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-[#005eb8]">Featured employer partner</p>
                        <h3 class="mt-2 text-2xl font-black text-slate-950">BMO Community Banking Careers</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">BMO connects Los Angeles candidates with customer-facing banking, branch leadership, small business, and operations career pathways.</p>
                    </div>
                </article>
                @endforelse
            </div>

            <aside class="featured-employer-aside">
                @include('site::partials.google-ad-placeholder', [
                    'format' => 'display',
                    'placement' => 'home-featured-employer-sidebar-top',
                    'label' => 'Hiring partner sponsor ad',
                ])
                <p class="community-kicker">Direct Employer Access</p>
                <h3 class="mt-2 text-lg font-black text-slate-950">Find jobs by hiring partner</h3>
                <div class="mt-4">
                    @if($featuredEmployerCards->isNotEmpty())
                    <label for="featured-employer-jump" class="sr-only">Select featured employer</label>
                    <select id="featured-employer-jump" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-800" onchange="if (this.value) window.location.href = this.value;">
                        <option value="">Choose an employer</option>
                        @foreach($featuredEmployerCards as $featuredEmployer)
                            <option value="{{ route('listings.index', ['user' => $featuredEmployer->getKey()]) }}">{{ $featuredEmployer->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-4 space-y-2">
                        @foreach($featuredEmployerCards as $featuredEmployer)
                            <a href="{{ route('listings.index', ['user' => $featuredEmployer->getKey()]) }}" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-bold text-slate-800 hover:border-[#005eb8]">
                                <span>{{ $featuredEmployer->name }}</span>
                                <span class="shrink-0 text-xs text-slate-500">{{ number_format((int) $featuredEmployer->active_listings_count) }} jobs</span>
                            </a>
                        @endforeach
                    </div>
                    @else
                    <a href="{{ route('listings.index') }}" class="community-secondary-button justify-center">Browse all employers</a>
                    @endif
                </div>
                @include('site::partials.google-ad-placeholder', [
                    'format' => 'display',
                    'placement' => 'home-featured-employer-display',
                    'label' => 'Featured sponsor ad',
                ])
            </aside>
        </div>
    </section>

    <section class="community-outreach-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <article class="community-outreach-card rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="community-kicker">Access</p>
            <h2 class="mt-3 text-xl font-black text-[var(--oc-text)]">Good jobs close to home</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">Roles across healthcare, education, banking, trades, media, hospitality, public service, and technology.</p>
        </article>
        <article class="community-outreach-card rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="community-kicker">Trust</p>
            <h2 class="mt-3 text-xl font-black text-[var(--oc-text)]">Employers show up clearly</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">Hiring teams can share mission, pay, benefits, location, and next steps before applicants apply.</p>
        </article>
        <article class="community-outreach-card rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="community-kicker">Outreach</p>
            <h2 class="mt-3 text-xl font-black text-[var(--oc-text)]">Hiring events and training</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">A natural home for resume help, career fairs, certification programs, and workforce partners.</p>
        </article>
        <article class="community-outreach-card rounded-2xl border border-[var(--oc-border)] bg-[var(--oc-surface)] p-5">
            <p class="community-kicker">Impact</p>
            <h2 class="mt-3 text-xl font-black text-[var(--oc-text)]">Families and futures</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--oc-muted)]">A pathway from better work to financial stability, stronger families, and stronger neighborhoods.</p>
        </article>
    </section>

    @include('site::partials.broadstreet-ad', [
        'zone' => 'inline',
        'format' => 'inline',
        'placement' => 'home-after-community-outreach',
    ])

    @include('site::partials.google-ad-placeholder', [
        'format' => 'leaderboard',
        'placement' => 'home-midpage-leaderboard',
        'label' => 'Community jobs sponsor ad',
    ])

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
                $categorySlug = (string) $category->slug;
                $categoryName = strtolower((string) $category->name);
                $iconComponent = match (true) {
                    str_contains($categorySlug, 'healthcare') || str_contains($categoryName, 'health') => 'heroicon-o-heart',
                    str_contains($categorySlug, 'education') || str_contains($categoryName, 'education') => 'heroicon-o-academic-cap',
                    str_contains($categorySlug, 'skilled-trades') || str_contains($categoryName, 'trade') => 'heroicon-o-wrench-screwdriver',
                    str_contains($categorySlug, 'media-creative') || str_contains($categoryName, 'media') || str_contains($categoryName, 'creative') => 'heroicon-o-megaphone',
                    str_contains($categorySlug, 'public-sector') || str_contains($categoryName, 'public') => 'heroicon-o-building-office-2',
                    str_contains($categorySlug, 'nonprofit') || str_contains($categoryName, 'nonprofit') => 'heroicon-o-users',
                    str_contains($categorySlug, 'hospitality') || str_contains($categoryName, 'hospitality') => 'heroicon-o-building-storefront',
                    str_contains($categorySlug, 'technology') || str_contains($categoryName, 'technology') => 'heroicon-o-computer-desktop',
                    default => 'heroicon-o-briefcase',
                };
            @endphp
            <a href="{{ route('listings.index', ['category' => $category->id]) }}" class="oc-pill">
                @if($categoryIconUrl)
                    <img src="{{ $categoryIconUrl }}" alt="" class="h-4 w-4 object-contain">
                @else
                    <x-dynamic-component :component="$iconComponent" class="h-4 w-4 stroke-[1.8]" aria-hidden="true" />
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
                $featuredEmployerBrand = \Modules\User\App\Support\FeaturedEmployerBrand::forName($employerName);
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
                        @if($featuredEmployerBrand)
                        @include('listing::partials.featured-employer-badge', ['employerName' => $employerName, 'employer' => $listing->user])
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

    <section class="community-cta-band rounded-2xl border border-[var(--oc-border)] px-6 py-8 md:px-10 md:py-12">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-semibold text-[var(--oc-text)]">{{ __('site::messages.sell_something') }}</h2>
                <p class="text-[var(--oc-muted)] mt-2 text-sm md:text-base">Post a local job, hiring event, or career pathway and reach Sentinel readers who are ready for the next step.</p>
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
