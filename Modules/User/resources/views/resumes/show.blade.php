@extends('app::layouts.app')

@section('title', $profile->user?->name.' Resume')

@section('content')
<div class="mx-auto max-w-[960px] px-4 py-8">
    <a href="{{ route('resumes.index') }}" class="text-sm font-bold text-[#bc422f]">← Back to resume bank</a>

    <section class="mt-4 overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm">
        <div class="bg-slate-950 p-7 text-white md:p-9">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#f58a72]">Candidate authorized employer review</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight">{{ $profile->user?->name }}</h1>
            <p class="mt-2 text-sm font-semibold text-slate-300">{{ collect([$profile->city, $profile->country])->filter()->join(', ') ?: 'Location available on request' }}</p>
        </div>

        <div class="grid gap-7 p-7 md:grid-cols-[1fr,260px] md:p-9">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Candidate profile</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $profile->bio ?: 'Review the authorized resume for this candidate’s experience, skills, and qualifications.' }}</p>
                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">Opening this profile was recorded and is visible to the candidate.</div>
            </div>

            <aside class="rounded-[24px] border border-[#f4b5a7] bg-[#fff3ef] p-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#bc422f]">Private document</p>
                <p class="mt-3 break-words text-sm font-semibold text-slate-950">{{ $profile->resume_original_name }}</p>
                <p class="mt-1 text-xs text-slate-500">Uploaded {{ $profile->resume_uploaded_at?->format('M j, Y') }}</p>
                <a href="{{ route('resumes.download', $profile) }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-full bg-[#e65f45] px-5 text-sm font-bold text-white">Download resume</a>
                <p class="mt-3 text-xs leading-5 text-slate-500">The candidate will see that your employer account downloaded this file.</p>
            </aside>
        </div>
    </section>
</div>
@endsection
