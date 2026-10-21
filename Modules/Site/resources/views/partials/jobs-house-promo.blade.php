@php
    $promo = $promo ?? 'resume';
@endphp

@if($promo === 'sponsor')
<aside class="jobs-house-promo jobs-house-promo--sponsor" aria-label="Hire through LA Sentinel Jobs">
    <img src="{{ asset('images/la-sentinel/logo.webp') }}" alt="Los Angeles Sentinel" class="jobs-house-promo__logo">
    <h3>Hiring in Los Angeles?</h3>
    <p>Put your openings in front of Sentinel readers. Featured employers get a branded profile, top placement, and a count of applicants sent your way.</p>
    <p class="jobs-house-promo__proof">Join St. John's Community Health, our founding sponsor.</p>
    <a href="{{ route('partners.inquiry') }}" class="jobs-house-promo__button">Become a hiring partner</a>
</aside>
@else
<aside class="jobs-house-promo jobs-house-promo--resume" aria-label="Post your resume">
    <h3>Let employers find you.</h3>
    <p>Post your resume once. When you choose, verified local employers can see it and reach out first.</p>
    <ul>
        <li>Free for job seekers</li>
        <li>Takes about two minutes</li>
        <li>You control who sees it</li>
    </ul>
    <a href="{{ route('resume.post', ['src' => 'jobs-sidebar']) }}" class="jobs-house-promo__button">Post your resume</a>
</aside>
@endif
