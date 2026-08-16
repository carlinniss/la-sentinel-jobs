@extends('app::layouts.app')

@section('title', 'Resume Bank')

@section('content')
<div class="mx-auto max-w-[1280px] px-4 py-8">
    <div class="grid gap-6 xl:grid-cols-[280px,minmax(0,1fr)]">
        <div>
            @include('panel::partials.sidebar', ['activeMenu' => 'resume-bank'])
        </div>

        <section>
            <div class="rounded-[30px] bg-slate-950 p-6 text-white md:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#f58a72]">Authorized employer access</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight md:text-4xl">Candidate resume bank</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-300">Every candidate shown here explicitly authorized verified employers to discover their resume. Downloads and profile views are recorded for the candidate.</p>
                <form method="get" action="{{ route('resumes.index') }}" class="mt-6 flex max-w-2xl gap-2">
                    <input name="q" value="{{ $search }}" placeholder="Search name, location, or experience" class="min-h-12 flex-1 rounded-full border border-white/15 bg-white px-5 text-sm text-slate-950">
                    <button class="rounded-full bg-[#e65f45] px-5 text-sm font-bold text-white">Search</button>
                </form>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($profiles as $profile)
                    <a href="{{ route('resumes.show', $profile) }}" class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-[#f4b5a7] hover:shadow-md">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-lg font-bold text-white">{{ mb_strtoupper(mb_substr((string) $profile->user?->name, 0, 1)) }}</div>
                        <h2 class="mt-4 text-lg font-bold text-slate-950">{{ $profile->user?->name }}</h2>
                        <p class="mt-1 text-sm font-semibold text-[#bc422f]">{{ $profile->city ?: 'Location available on request' }}</p>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit((string) ($profile->bio ?: 'Candidate has authorized employer review of their uploaded resume.'), 150) }}</p>
                        <p class="mt-4 text-xs font-semibold text-slate-400">Updated {{ $profile->resume_uploaded_at?->diffForHumans() }}</p>
                    </a>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-[26px] border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">No discoverable resumes match this search.</div>
                @endforelse
            </div>

            <div class="mt-6">{{ $profiles->links() }}</div>
        </section>
    </div>
</div>
@endsection
