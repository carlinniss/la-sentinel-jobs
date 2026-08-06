@php
    $variant = $variant ?? 'card';
    $employerName = trim((string) ($employerName ?? 'BMO Community Banking Careers'));
    $profile = $profile ?? null;
    $bio = trim((string) ($profile?->bio ?? ''));
    $website = trim((string) ($profile?->website ?? ''));
@endphp

@if ($variant === 'detail')
    <div class="rounded-2xl border border-[#cfe2f3] bg-gradient-to-br from-white via-[#f8fbff] to-[#eef6ff] p-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="flex h-14 w-24 shrink-0 items-center justify-center rounded-xl border border-[#d8e8f8] bg-white px-3 shadow-sm">
                <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-8 w-auto">
            </div>
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
            @if ($website !== '')
                <a href="{{ $website }}" target="_blank" rel="noopener" class="rounded-full bg-[#005eb8] px-3 py-1 text-xs font-bold text-white">BMO careers</a>
            @endif
        </div>
    </div>
@else
    <div class="inline-flex min-w-0 max-w-full items-center gap-2 rounded-full border border-[#d8e8f8] bg-white px-2.5 py-1.5 shadow-sm">
        <span class="flex h-6 w-16 shrink-0 items-center justify-center">
            <img src="{{ asset('images/employers/bmo.svg') }}" alt="BMO" class="h-5 w-auto">
        </span>
        <span class="truncate text-xs font-bold text-slate-700">{{ $employerName }}</span>
    </div>
@endif
