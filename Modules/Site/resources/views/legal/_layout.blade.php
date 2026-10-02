@extends('app::layouts.app')

@section('content')
<div class="bg-[#f4f0e8] px-4 pb-12 pt-8 sm:pt-12">
    <article class="mx-auto max-w-[720px] rounded-[28px] border border-[#e6d8bd] bg-[#fffdf8] p-6 text-[#17120f] sm:p-10">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#8b1d22]">LA Sentinel Jobs</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight">@yield('legal_title')</h1>
        <p class="mt-1 text-sm text-[#675d52]">Last updated October 2, 2026</p>
        <div class="mt-6 space-y-5 text-base leading-7 [&_h2]:mt-8 [&_h2]:text-lg [&_h2]:font-bold [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5 [&_a]:font-semibold [&_a]:text-[#8b1d22] [&_a]:underline">
            @yield('legal_body')
        </div>
        <p class="mt-10 text-sm text-[#675d52]">
            <a href="{{ route('resume.post') }}" class="font-semibold text-[#8b1d22] underline">Back to Post Your Resume</a>
        </p>
    </article>
</div>
@endsection
