@php
    $variant = $variant ?? 'card';
    $employerName = trim((string) ($employerName ?? 'Featured Employer'));
    $profile = $profile ?? null;
    $bio = trim((string) ($profile?->bio ?? ''));
    $website = trim((string) ($profile?->website ?? ''));
    $employer = $employer ?? null;
    $profileUrl = $employer ? route('employers.show', $employer) : null;
    $brand = \Modules\User\App\Support\FeaturedEmployerBrand::forName($employerName);
    $primary = $brand['primary'] ?? '#005eb8';
    $soft = $brand['soft'] ?? '#eef6ff';
    $softBorder = $brand['soft_border'] ?? '#b9d8f2';
@endphp

@if ($brand && $variant === 'feature-strip')
    <div class="rounded-2xl p-4 shadow-sm" style="border: 1px solid {{ $softBorder }}; background: linear-gradient(90deg, {{ $soft }}, #fff, #fff8f6);">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <a @if($profileUrl) href="{{ $profileUrl }}" @endif class="flex items-center gap-4" aria-label="View {{ $employerName }} employer profile">
                <span class="flex min-h-20 w-40 shrink-0 items-center justify-center rounded-2xl bg-white px-5 py-4 shadow-sm" style="border: 1px solid {{ $softBorder }};">
                    <img src="{{ asset($brand['logo']) }}" alt="{{ $brand['logo_alt'] }}" class="h-14 w-auto max-w-full object-contain">
                </span>
                <span>
                    <span class="block text-xs font-black uppercase tracking-[0.22em]" style="color: {{ $primary }};">Featured employer partner</span>
                    <span class="mt-1 block text-xl font-black text-slate-950">{{ $employerName }}</span>
                    <span class="mt-1 block text-sm font-semibold text-slate-600">{{ $brand['summary'] }}</span>
                </span>
            </a>

            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="inline-flex h-11 shrink-0 items-center justify-center rounded-full px-5 text-sm font-black text-white transition hover:opacity-90" style="background: {{ $primary }};">
                    View featured profile
                </a>
            @endif
        </div>
    </div>
@elseif ($brand && $variant === 'detail')
    <div class="rounded-2xl p-4 shadow-sm" style="border: 1px solid {{ $softBorder }}; background: linear-gradient(135deg, #fff, {{ $soft }});">
        <div class="space-y-4">
            <a @if($profileUrl) href="{{ $profileUrl }}" @endif class="flex min-h-24 items-center justify-center rounded-2xl bg-white px-5 py-4 shadow-sm" style="border: 1px solid {{ $softBorder }};" aria-label="View {{ $employerName }} employer profile">
                <img src="{{ asset($brand['logo']) }}" alt="{{ $brand['logo_alt'] }}" class="h-16 w-auto max-w-full object-contain">
            </a>
            <div class="min-w-0">
                <p class="text-[0.68rem] font-bold uppercase tracking-[0.2em]" style="color: {{ $primary }};">Featured employer partner</p>
                <h3 class="mt-1 text-base font-bold leading-tight text-slate-950">{{ $employerName }}</h3>
                <p class="mt-1 text-sm leading-5 text-slate-600">
                    {{ $bio !== '' ? \Illuminate\Support\Str::limit($bio, 230) : $brand['detail_summary'] }}
                </p>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($brand['tags'] as $tag)
                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold" style="color: {{ $primary }}; box-shadow: 0 0 0 1px {{ $softBorder }};">{{ $tag }}</span>
            @endforeach
            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="rounded-full bg-white px-3 py-1 text-xs font-bold" style="color: {{ $primary }}; box-shadow: 0 0 0 1px {{ $softBorder }};">Employer profile</a>
            @endif
            @if ($website !== '')
                <a href="{{ $website }}" target="_blank" rel="noopener" class="rounded-full px-3 py-1 text-xs font-bold text-white" style="background: {{ $primary }};">{{ $brand['careers_label'] }}</a>
            @endif
        </div>
    </div>
@elseif ($brand)
    <a @if($profileUrl) href="{{ $profileUrl }}" @endif aria-label="View {{ $employerName }} employer profile" class="inline-flex min-w-0 max-w-full items-center gap-2 rounded-2xl px-2.5 py-2 shadow-sm" style="border: 1px solid {{ $softBorder }}; background: {{ $soft }};">
        <span class="flex h-8 w-24 shrink-0 items-center justify-center">
            <img src="{{ asset($brand['logo']) }}" alt="{{ $brand['logo_alt'] }}" class="h-7 w-auto max-w-full object-contain">
        </span>
        <span class="min-w-0">
            <span class="block truncate text-[0.62rem] font-black uppercase tracking-[0.16em]" style="color: {{ $primary }};">Featured employer</span>
            <span class="block truncate text-xs font-bold text-slate-800">{{ $employerName }}</span>
        </span>
    </a>
@endif
