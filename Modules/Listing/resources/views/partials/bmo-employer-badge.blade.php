@php
    $variant = $variant ?? 'card';
    $employerName = trim((string) ($employerName ?? 'BMO Community Banking Careers'));
    $profile = $profile ?? null;
    $bio = trim((string) ($profile?->bio ?? ''));
    $website = trim((string) ($profile?->website ?? ''));
    $employer = $employer ?? null;
    $profileUrl = $employer ? route('employers.show', $employer) : null;
@endphp

@if ($variant === 'feature-strip')
    <div class="rounded-2xl border border-[#b9d8f2] bg-gradient-to-r from-[#eef6ff] via-white to-[#fff5f5] p-4 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="flex items-center gap-4" aria-label="View {{ $employerName }} employer profile">
                    <span class="flex min-h-20 w-40 shrink-0 items-center justify-center rounded-2xl border border-[#d8e8f8] bg-white px-5 py-4 shadow-sm">
                        <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-14 w-auto max-w-full">
                    </span>
                    <span>
                        <span class="block text-xs font-black uppercase tracking-[0.22em] text-[#005eb8]">Featured employer partner</span>
                        <span class="mt-1 block text-xl font-black text-slate-950">{{ $employerName }}</span>
                        <span class="mt-1 block text-sm font-semibold text-slate-600">BMO is hiring through LA Sentinel Jobs with community banking roles and advancement pathways.</span>
                    </span>
                </a>
            @else
                <div class="flex items-center gap-4">
                    <span class="flex min-h-20 w-40 shrink-0 items-center justify-center rounded-2xl border border-[#d8e8f8] bg-white px-5 py-4 shadow-sm">
                        <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-14 w-auto max-w-full">
                    </span>
                    <span>
                        <span class="block text-xs font-black uppercase tracking-[0.22em] text-[#005eb8]">Featured employer partner</span>
                        <span class="mt-1 block text-xl font-black text-slate-950">{{ $employerName }}</span>
                        <span class="mt-1 block text-sm font-semibold text-slate-600">Community banking roles and advancement pathways.</span>
                    </span>
                </div>
            @endif

            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="inline-flex h-11 shrink-0 items-center justify-center rounded-full bg-[#005eb8] px-5 text-sm font-black text-white hover:bg-[#004b93]">
                    View featured profile
                </a>
            @endif
        </div>
    </div>
@elseif ($variant === 'detail')
    <div class="rounded-2xl border border-[#cfe2f3] bg-gradient-to-br from-white via-[#f8fbff] to-[#eef6ff] p-4 shadow-sm">
        <div class="space-y-4">
            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="flex min-h-24 items-center justify-center rounded-2xl border border-[#d8e8f8] bg-white px-5 py-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#005eb8] hover:shadow-md" aria-label="View {{ $employerName }} employer profile">
                    <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-16 w-auto max-w-full">
                </a>
            @else
                <div class="flex min-h-24 items-center justify-center rounded-2xl border border-[#d8e8f8] bg-white px-5 py-4 shadow-sm">
                    <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-16 w-auto max-w-full">
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-[0.68rem] font-bold uppercase tracking-[0.2em] text-[#005eb8]">Featured employer partner</p>
                <h3 class="mt-1 text-base font-bold leading-tight text-slate-950">{{ $employerName }}</h3>
                <p class="mt-1 text-sm leading-5 text-slate-600">
                    {{ $bio !== '' ? \Illuminate\Support\Str::limit($bio, 230) : 'Community banking roles with local customer impact, training, benefits, and clear advancement paths.' }}
                </p>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#005eb8] ring-1 ring-[#d8e8f8]">Banking careers</span>
            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#005eb8] ring-1 ring-[#d8e8f8]">Los Angeles hiring</span>
            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#005eb8] ring-1 ring-[#d8e8f8]">Employer profile</a>
            @endif
            @if ($website !== '')
                <a href="{{ $website }}" target="_blank" rel="noopener" class="rounded-full bg-[#005eb8] px-3 py-1 text-xs font-bold text-white">BMO careers</a>
            @endif
        </div>
    </div>
@else
    @if ($profileUrl)
        <a href="{{ $profileUrl }}" aria-label="View {{ $employerName }} employer profile" class="inline-flex min-w-0 max-w-full items-center gap-2 rounded-2xl border border-[#b9d8f2] bg-[#f8fbff] px-2.5 py-2 shadow-sm transition hover:border-[#005eb8] hover:shadow-md">
            <span class="flex h-8 w-24 shrink-0 items-center justify-center">
                <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-7 w-auto">
            </span>
            <span class="min-w-0">
                <span class="block truncate text-[0.62rem] font-black uppercase tracking-[0.16em] text-[#005eb8]">Featured employer</span>
                <span class="block truncate text-xs font-bold text-slate-800">{{ $employerName }}</span>
            </span>
        </a>
    @else
        <div class="inline-flex min-w-0 max-w-full items-center gap-2 rounded-2xl border border-[#b9d8f2] bg-[#f8fbff] px-2.5 py-2 shadow-sm">
            <span class="flex h-8 w-24 shrink-0 items-center justify-center">
                <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-7 w-auto">
            </span>
            <span class="min-w-0">
                <span class="block truncate text-[0.62rem] font-black uppercase tracking-[0.16em] text-[#005eb8]">Featured employer</span>
                <span class="block truncate text-xs font-bold text-slate-800">{{ $employerName }}</span>
            </span>
        </div>
    @endif
@endif
