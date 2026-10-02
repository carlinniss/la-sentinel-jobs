@extends('app::layouts.app')

@section('title', 'You\'re All Set')

@section('content')
@php
    $hasResume = (bool) $profile?->hasResume();
    $boardEnabled = \Modules\Site\App\Http\Middleware\ResumeOnlyMode::boardEnabled();
    $firstName = \Illuminate\Support\Str::of((string) $user->name)->before(' ')->toString();
    $secondaryButton = 'flex min-h-[52px] w-full items-center justify-center rounded-2xl border-2 border-[#8b1d22] bg-white px-6 text-base font-bold text-[#8b1d22] hover:bg-[#fff0d2]';
    $primaryButton = 'flex min-h-[56px] w-full items-center justify-center rounded-2xl bg-[#8b1d22] px-6 text-lg font-bold text-white shadow-sm hover:bg-[#5b1014]';
@endphp
<div class="bg-[#f4f0e8] px-4 pb-12 pt-8 sm:pt-12">
    <div class="mx-auto max-w-[560px] rounded-[28px] border border-[#e6d8bd] bg-[#fffdf8] p-6 text-center shadow-[0_20px_50px_-30px_rgba(23,18,15,0.45)] sm:p-8">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#e8f3ea] text-[#1f7a3a]">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m5 12.5 4.5 4.5L19 7.5"/>
            </svg>
        </div>

        @if(session('resume_notice'))
            <div class="mt-5 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-left text-sm leading-6 text-amber-900" role="status">{{ session('resume_notice') }}</div>
        @endif

        @if($hasResume)
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-[#17120f]">You're all set{{ $firstName !== '' ? ', '.$firstName : '' }}</h1>
            <p class="mt-3 text-base leading-7 text-[#675d52]">
                Your resume is saved.
                {{ $profile->resume_searchable ? 'Verified employers can now find it.' : 'It is private until you choose to let verified employers find it.' }}
            </p>
        @else
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-[#17120f]">Your account is ready{{ $firstName !== '' ? ', '.$firstName : '' }}</h1>
            <p class="mt-3 text-base leading-7 text-[#675d52]">
                Add your resume whenever it's handy. We're emailing a link to {{ $user->email }} so you can finish from any device.
            </p>
        @endif

        <div class="mt-7 space-y-3">
            @if($hasResume)
                @if($boardEnabled)
                <a href="{{ route('listings.index') }}" class="{{ $primaryButton }}">Browse jobs</a>
                @endif
                <a href="{{ route('panel.profile.edit') }}" class="{{ $boardEnabled ? $secondaryButton : $primaryButton }}">Complete my profile</a>
            @else
                <a href="{{ route('resume.post') }}" class="{{ $primaryButton }}">Upload my resume now</a>
                @if($boardEnabled)
                <a href="{{ route('listings.index') }}" class="{{ $secondaryButton }}">Browse jobs</a>
                @endif
            @endif
            @unless($boardEnabled)
                <p class="pt-2 text-sm leading-6 text-[#675d52]">The LA Sentinel Jobs board opens soon. We'll email you when jobs go live.</p>
            @endunless
        </div>
    </div>
</div>
@endsection
