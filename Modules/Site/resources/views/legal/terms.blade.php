@extends('site::legal._layout')

@section('title', 'Terms of Use')
@section('legal_title', 'Terms of Use')

@section('legal_body')
<p>These terms apply when you use LA Sentinel Jobs, a community employment service of the Los Angeles Sentinel. By creating an account you agree to them.</p>

<h2>Your account</h2>
<ul>
    <li>Give accurate information and keep your password private.</li>
    <li>Only upload a resume that is yours and that you have the right to share.</li>
    <li>Do not use the service to mislead, harass, or collect information about other people.</li>
</ul>

<h2>Employers and hiring</h2>
<p>LA Sentinel Jobs helps connect job seekers with employers. We do not guarantee interviews, job offers, or employment, and employers are responsible for their own hiring decisions. Only employers we have verified can be given access to resumes that their owners have chosen to make discoverable.</p>

<h2>Content</h2>
<p>We may remove resumes, profiles, or listings that are inaccurate, inappropriate, or violate these terms, and we may suspend accounts that misuse the service.</p>

<h2>Privacy</h2>
<p>How we handle your information is described in our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>

<h2>Changes</h2>
<p>We may update these terms as the service grows. The date above shows when they last changed.</p>

<h2>Contact</h2>
<p><a href="mailto:{{ config('resume.contact_email') }}">{{ config('resume.contact_email') }}</a></p>
@endsection
