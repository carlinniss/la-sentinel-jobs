@extends('app::layouts.app')

@section('title', 'Partner Inquiry')

@section('content')
<div class="partner-inquiry-page mx-auto max-w-[1120px] px-4 py-8 md:py-14">
    <section class="partner-inquiry-shell">
        <div class="partner-inquiry-copy">
            <p class="community-kicker">Partner With LA Sentinel Jobs</p>
            <h1>Bring hiring events, workforce programs, and career pathways to the community.</h1>
            <p>
                Use this form to start a conversation about reaching LA Sentinel Jobs readers, jobseekers, employers, and community workforce partners.
            </p>
            <div class="partner-inquiry-benefits" aria-label="Partner benefits">
                <div>
                    <strong>Jobs page visibility</strong>
                    <span>Featured employer placement, sponsored ad areas, dedicated partner profiles, and direct links to active roles or hiring pages.</span>
                </div>
                <div>
                    <strong>Newsletter promotion</strong>
                    <span>Opportunities to promote jobs, hiring events, training programs, and workforce campaigns through LA Sentinel audience channels.</span>
                </div>
                <div>
                    <strong>Editorial and community storytelling</strong>
                    <span>Potential coverage around hiring initiatives, career pathways, employer spotlights, and community impact stories.</span>
                </div>
                <div>
                    <strong>Applicant attribution</strong>
                    <span>A clearer way to see which candidates and employer relationships started through LA Sentinel Jobs.</span>
                </div>
                <div class="partner-inquiry-benefit-featured">
                    <strong>Early partner access to upcoming AI upgrades</strong>
                    <span>As new AI tools roll out, partners can be among the first considered for features that help improve job matching, candidate discovery, resume support, employer spotlights, and campaign reporting.</span>
                </div>
            </div>
            <p class="partner-inquiry-note">
                Tell us who you are, how to reach you, and what kind of partnership you want to explore. The LA Sentinel team will receive your inquiry directly.
            </p>
        </div>

        <form method="POST" action="{{ route('partners.inquiry.store') }}" class="partner-inquiry-form">
            @csrf

            @if(session('success'))
            <div class="partner-inquiry-success">
                {{ session('success') }}
            </div>
            @endif

            <label class="partner-inquiry-field">
                <span>Company name</span>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required maxlength="160">
                @error('company_name')
                    <strong>{{ $message }}</strong>
                @enderror
            </label>

            <label class="partner-inquiry-field">
                <span>Contact name</span>
                <input type="text" name="contact_name" value="{{ old('contact_name') }}" required maxlength="160">
                @error('contact_name')
                    <strong>{{ $message }}</strong>
                @enderror
            </label>

            <label class="partner-inquiry-field">
                <span>Contact information</span>
                <textarea name="contact_information" rows="4" required maxlength="1000" placeholder="Email, phone number, preferred contact method, and best time to reach you.">{{ old('contact_information') }}</textarea>
                @error('contact_information')
                    <strong>{{ $message }}</strong>
                @enderror
            </label>

            <label class="partner-inquiry-field">
                <span>Comments</span>
                <textarea name="comments" rows="5" maxlength="3000" placeholder="Tell us whether you are interested in job listings, featured employer placement, newsletter promotion, editorial opportunities, ads, hiring events, training programs, upcoming AI partner tools, or another workforce partnership.">{{ old('comments') }}</textarea>
                @error('comments')
                    <strong>{{ $message }}</strong>
                @enderror
            </label>

            <button type="submit" class="partner-inquiry-submit">Send Partner Inquiry</button>
        </form>
    </section>
</div>
@endsection
