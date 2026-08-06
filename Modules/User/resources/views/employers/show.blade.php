@extends('app::layouts.app')

@section('title', $employer->name)

@section('content')
@php
    $profile = $employer->profile;
    $employerName = trim((string) $employer->name);
    $isBmoEmployer = str_contains(strtolower($employerName), 'bmo');
    $bio = trim((string) ($profile?->bio ?? ''));
    $website = trim((string) ($profile?->website ?? ''));
    $location = trim(collect([$profile?->city, $profile?->country])->filter()->join(', '));
    $sentinelUrl = 'https://lasentinel.net';
    $proofPoints = $isBmoEmployer
        ? ['Top 10 North American bank', '13 million customers', 'Founded in 1817', 'Community banking roles']
        : ['Verified employer', 'Local hiring', 'Active opportunities'];
    $formatCompensation = static function ($listing): string {
        if (is_null($listing->price) || (float) $listing->price <= 0) {
            return __('listing::messages.price_on_request');
        }

        $amount = (float) $listing->price;
        $prefix = ($listing->currency ?? 'USD') === 'USD' ? '$' : '';
        $suffix = $amount < 1000 ? '/hr' : '/yr';

        return $prefix.number_format($amount, 0).$suffix;
    };
@endphp

<div class="mx-auto max-w-[1180px] px-4 py-8">
    <div class="mb-5 flex flex-col gap-3 rounded-2xl border border-[#d9c08a] bg-[#fffaf0] px-4 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ $sentinelUrl }}" target="_blank" rel="noopener" class="flex items-center gap-3">
            <span class="rounded-xl bg-white px-3 py-2 shadow-sm">
                <img src="{{ asset('images/la-sentinel/logo.webp') }}" alt="Los Angeles Sentinel" class="h-11 w-auto">
            </span>
            <span>
                <span class="block text-sm font-extrabold text-[#7b1a1f]">LA Sentinel Jobs employer profile</span>
                <span class="block text-xs font-semibold text-slate-600">Connecting Sentinel readers with local hiring partners.</span>
            </span>
        </a>
        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-black px-2.5 py-1.5 text-xs font-bold text-white">
            <img src="{{ asset('images/bakewell-media/logo.png') }}" alt="Bakewell Media" class="h-9 w-9 rounded-full object-contain">
            Bakewell Media
        </span>
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="grid lg:grid-cols-[1fr,1.15fr]">
            <div class="bg-[#eef6ff] p-6 md:p-8">
                <div class="flex min-h-64 items-center justify-center rounded-3xl border border-[#d8e8f8] bg-white p-10 shadow-sm">
                    @if($isBmoEmployer)
                        <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-32 w-auto max-w-full">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-slate-950 text-4xl font-bold text-white">
                            {{ mb_strtoupper(mb_substr($employerName, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-6 md:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#005eb8]">Employer profile</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight text-slate-950">{{ $employerName }}</h1>
                @if($location !== '')
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ $location }}</p>
                @endif
                <p class="mt-5 text-base leading-7 text-slate-600">
                    {{ $bio !== '' ? $bio : 'This employer is hiring through LA Sentinel Jobs.' }}
                </p>
                <div class="mt-6 grid gap-2 sm:grid-cols-2">
                    @foreach($proofPoints as $point)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ $point }}</div>
                    @endforeach
                </div>
                <div class="mt-6 flex flex-wrap gap-2">
                    <span class="rounded-full bg-[#eef6ff] px-3 py-1.5 text-xs font-bold text-[#005eb8] ring-1 ring-[#d8e8f8]">{{ number_format($listings->total()) }} active jobs</span>
                    @if($profile?->is_verified)
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">Verified employer</span>
                    @endif
                    @if($website !== '')
                        <a href="{{ $website }}" target="_blank" rel="noopener" class="rounded-full bg-[#005eb8] px-3 py-1.5 text-xs font-bold text-white">Careers site</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="mt-8">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-2xl font-bold text-slate-950">Open roles</h2>
            <a href="{{ route('listings.index', ['user' => $employer->getKey()]) }}" class="text-sm font-bold text-[#005eb8]">View all listings</a>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($listings as $listing)
                <a href="{{ route('listings.show', $listing) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-[#cfe2f3] hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ $listing->category?->name ?? 'Job' }}</p>
                        @if($listing->is_featured)
                            <span class="rounded-full bg-[#eef6ff] px-2.5 py-1 text-[11px] font-bold text-[#005eb8]">Featured</span>
                        @endif
                    </div>
                    <h3 class="mt-3 text-lg font-bold leading-tight text-slate-950 group-hover:text-[#005eb8]">{{ $listing->title }}</h3>
                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <p class="text-sm font-semibold text-[#005eb8]">{{ $formatCompensation($listing) }}</p>
                        <p class="truncate text-sm text-slate-500">{{ collect([$listing->city, $listing->country])->filter()->join(', ') }}</p>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-8 text-slate-500">No active jobs yet.</div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $listings->links() }}
        </div>
    </section>
</div>
@endsection
