@extends('app::layouts.app')

@section('title', 'Post Your Resume')


@section('content')
@php
    $hasResume = (bool) $profile?->hasResume();
    $source = (string) session('resume_signup_source', '');
    $isEventVisitor = str_starts_with($source, 'tos');
    $mode = old('auth_mode') === 'login' ? 'login' : 'signup';
    $isFree = $feeCents === 0;
    $inputClass = 'block w-full rounded-2xl border border-[#d8c7a8] bg-white px-4 py-3.5 text-base text-[#17120f] placeholder:text-[#9a8f82] focus:border-[#8b1d22] focus:outline-none focus:ring-2 focus:ring-[#8b1d22]/20';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-[#17120f]';
    $errorClass = 'mt-1.5 text-sm font-medium text-[#b42318]';
    $primaryButton = 'flex min-h-[56px] w-full items-center justify-center rounded-2xl bg-[#8b1d22] px-6 text-lg font-bold text-white shadow-sm hover:bg-[#5b1014] focus:outline-none focus:ring-4 focus:ring-[#8b1d22]/25';
@endphp
<div class="bg-[#f4f0e8] pb-12">
    <section class="relative overflow-hidden bg-[#5b1014] text-white">
        <img src="{{ asset('images/la-sentinel/resume-pathway-campaign.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25">
        <div class="relative mx-auto max-w-[640px] px-4 pb-24 pt-8 sm:pt-12">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#f3d27a]">
                {{ $isEventVisitor ? 'Welcome, Taste of Soul' : 'LA Sentinel Jobs' }}
            </p>
            <h1 class="mt-2 text-[clamp(2rem,8vw,2.75rem)] font-extrabold leading-[1.05] tracking-tight">Post your resume</h1>
            <p class="mt-3 max-w-[34rem] text-base leading-7 text-white/90">
                @if($user)
                    Add or update your resume below. It stays private until you choose to let verified employers find it.
                @else
                    Create your account and add your resume in one step. No resume on your phone? Sign up now and add it later.
                @endif
            </p>
            <ul class="mt-4 flex flex-wrap gap-2 text-sm font-semibold">
                @if($isFree)
                <li class="rounded-full bg-white/15 px-3 py-1">Free</li>
                @endif
                <li class="rounded-full bg-white/15 px-3 py-1">Private by default</li>
                <li class="rounded-full bg-white/15 px-3 py-1">Local employers</li>
            </ul>
        </div>
    </section>

    <div class="relative mx-auto -mt-16 max-w-[640px] px-4">
        <div class="rounded-[28px] border border-[#e6d8bd] bg-[#fffdf8] p-5 shadow-[0_20px_50px_-30px_rgba(23,18,15,0.45)] sm:p-8">
            @if(session('resume_notice'))
                <div class="mb-5 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900" role="status">{{ session('resume_notice') }}</div>
            @endif

            @guest
            <div x-data="{ mode: '{{ $mode }}', fileName: '' }">
                <div class="mb-6 grid grid-cols-2 gap-1 rounded-2xl bg-[#f4ece0] p-1" role="tablist" aria-label="Account">
                    <button type="button" role="tab" x-on:click="mode = 'signup'" x-bind:aria-selected="mode === 'signup'"
                        x-bind:class="mode === 'signup' ? 'bg-white text-[#8b1d22] shadow-sm' : 'text-[#675d52]'"
                        class="min-h-[48px] rounded-xl px-3 text-base font-bold {{ $mode === 'signup' ? 'bg-white text-[#8b1d22] shadow-sm' : 'text-[#675d52]' }}">
                        New here
                    </button>
                    <button type="button" role="tab" x-on:click="mode = 'login'" x-bind:aria-selected="mode === 'login'"
                        x-bind:class="mode === 'login' ? 'bg-white text-[#8b1d22] shadow-sm' : 'text-[#675d52]'"
                        class="min-h-[48px] rounded-xl px-3 text-base font-bold {{ $mode === 'login' ? 'bg-white text-[#8b1d22] shadow-sm' : 'text-[#675d52]' }}">
                        Log in
                    </button>
                </div>

                <form method="POST" action="{{ route('resume.store') }}" enctype="multipart/form-data" class="space-y-5" x-show="mode === 'signup'" @if($mode !== 'signup') x-cloak style="display:none" @endif>
                    @csrf
                    <input type="hidden" name="auth_mode" value="signup">

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="{{ $labelClass }}">First name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" class="{{ $inputClass }}" autocomplete="given-name" required>
                            @error('first_name')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="{{ $labelClass }}">Last name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" class="{{ $inputClass }}" autocomplete="family-name" required>
                            @error('last_name')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="{{ $labelClass }}">Email</label>
                        <input id="email" name="email" type="email" inputmode="email" value="{{ $mode === 'signup' ? old('email') : '' }}" class="{{ $inputClass }}" autocomplete="email" autocapitalize="off" required>
                        @if($mode === 'signup')
                            @error('email')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div x-data="{ show: false }">
                        <label for="password" class="{{ $labelClass }}">Create a password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" x-bind:type="show ? 'text' : 'password'" class="{{ $inputClass }} pr-20" autocomplete="new-password" required>
                            <button type="button" x-on:click="show = !show" class="absolute inset-y-0 right-2 my-auto h-10 rounded-xl px-3 text-sm font-semibold text-[#8b1d22]" x-text="show ? 'Hide' : 'Show'">Show</button>
                        </div>
                        @if($mode === 'signup')
                            @error('password')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    @include('user::resumes.partials.post-file-field', ['optional' => true])

                    @include('user::resumes.partials.post-consent', ['checked' => (bool) old('resume_searchable', false)])

                    <div>
                        <label for="phone" class="{{ $labelClass }}">Phone <span class="font-normal text-[#675d52]">(optional)</span></label>
                        <input id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" class="{{ $inputClass }}" autocomplete="tel">
                        @error('phone')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 text-sm leading-6 text-[#17120f]">
                        <input type="checkbox" name="terms" value="1" @checked(old('terms')) required class="mt-0.5 h-5 w-5 shrink-0 rounded border-[#b9a98c] text-[#8b1d22] focus:ring-[#8b1d22]">
                        <span>I agree to the LA Sentinel Jobs terms of use.</span>
                    </label>
                    @error('terms')<p class="{{ $errorClass }} -mt-3">{{ $message }}</p>@enderror

                    <button type="submit" class="{{ $primaryButton }}">
                        <span x-text="fileName ? 'Create account & post resume' : 'Create my account'">Create account &amp; post resume</span>
                    </button>
                    <p class="text-center text-sm text-[#675d52]" x-show="!fileName">You can add your resume later. We'll email you a link.</p>
                </form>

                <form method="POST" action="{{ route('login') }}" class="space-y-5" x-show="mode === 'login'" @if($mode !== 'login') x-cloak style="display:none" @endif>
                    @csrf
                    <input type="hidden" name="auth_mode" value="login">
                    <input type="hidden" name="redirect" value="{{ route('resume.post', [], false) }}">

                    <p class="text-base leading-7 text-[#675d52]">Log in, then add your resume on the next screen.</p>

                    <div>
                        <label for="login_email" class="{{ $labelClass }}">Email</label>
                        <input id="login_email" name="email" type="email" inputmode="email" value="{{ $mode === 'login' ? old('email') : '' }}" class="{{ $inputClass }}" autocomplete="username" autocapitalize="off" required>
                        @if($mode === 'login')
                            @error('email')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div>
                        <label for="login_password" class="{{ $labelClass }}">Password</label>
                        <input id="login_password" name="password" type="password" class="{{ $inputClass }}" autocomplete="current-password" required>
                    </div>

                    <button type="submit" class="{{ $primaryButton }}">Log in</button>
                    <p class="text-center text-sm">
                        <a href="{{ route('password.request') }}" class="font-semibold text-[#8b1d22] underline-offset-4 hover:underline">Forgot your password?</a>
                    </p>
                </form>
            </div>
            @endguest

            @auth
            <div x-data="{ fileName: '' }">
                <div class="mb-5 rounded-2xl border border-[#e6d8bd] bg-[#fbf4e6] px-4 py-3.5">
                    @if($hasResume)
                        <p class="text-sm font-semibold text-[#17120f]">On file: {{ $profile->resume_original_name }}</p>
                        <p class="mt-0.5 text-sm text-[#675d52]">
                            Uploaded {{ $profile->resume_uploaded_at?->format('M j, Y') }} ·
                            {{ $profile->resume_searchable ? 'Visible to verified employers' : 'Private' }}
                        </p>
                    @else
                        <p class="text-sm font-semibold text-[#17120f]">Signed in as {{ $user->name }}</p>
                        <p class="mt-0.5 text-sm text-[#675d52]">No resume on file yet.</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('resume.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    @include('user::resumes.partials.post-file-field', ['optional' => false, 'replacing' => $hasResume])

                    @include('user::resumes.partials.post-consent', ['checked' => (bool) old('resume_searchable', $profile?->resume_searchable)])

                    <button type="submit" class="{{ $primaryButton }}">{{ $hasResume ? 'Save resume' : 'Post my resume' }}</button>
                </form>

                <p class="mt-5 text-center text-sm text-[#675d52]">
                    <a href="{{ route('panel.profile.edit') }}" class="font-semibold text-[#8b1d22] underline-offset-4 hover:underline">Manage your full profile</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ route('listings.index') }}" class="font-semibold text-[#8b1d22] underline-offset-4 hover:underline">Browse jobs</a>
                </p>
            </div>
            @endauth
        </div>

        <p class="mx-auto mt-5 max-w-[30rem] text-center text-sm leading-6 text-[#675d52]">
            Your resume is never public. Only verified employers can see it, and only if you allow it. You can change this or remove your resume anytime.
        </p>
    </div>
</div>
@endsection
