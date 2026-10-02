@extends('site::legal._layout')

@section('title', 'Privacy Policy')
@section('legal_title', 'Privacy Policy')

@section('legal_body')
<p>LA Sentinel Jobs is a community employment service of the Los Angeles Sentinel. This policy explains what we collect when you create an account or post a resume, and how it is used.</p>

<h2>What we collect</h2>
<ul>
    <li>Your name, email address, and password (stored in encrypted form).</li>
    <li>Your phone number, if you choose to give it.</li>
    <li>Your resume file, if you upload one, and its file name, type, and size.</li>
    <li>How you found us, such as an event QR code, so we can measure our outreach.</li>
    <li>A record of which verified employers viewed or downloaded your resume.</li>
</ul>

<h2>Who can see your resume</h2>
<p>Your resume is never public. It is stored privately and is hidden from employers by default. Only if you turn on "Let verified employers find my resume" can employers that we have verified and given resume-bank access find, view, or download it. You can see which employers viewed or downloaded it from your profile.</p>

<h2>How we use your information</h2>
<ul>
    <li>To run your account and show your resume to employers you allow.</li>
    <li>To email you about your account, your resume, and LA Sentinel Jobs opportunities.</li>
    <li>To understand how people find and use LA Sentinel Jobs.</li>
</ul>
<p>We do not sell your personal information.</p>

<h2>Your choices</h2>
<ul>
    <li>Turn employer discovery on or off at any time from your profile.</li>
    <li>Replace or remove your resume at any time. Removing it deletes the file from our system.</li>
    <li>Delete your account from your profile settings, or ask us to do it for you.</li>
</ul>

<h2>Contact</h2>
<p>Questions or requests about your information: <a href="mailto:{{ config('resume.contact_email') }}">{{ config('resume.contact_email') }}</a>.</p>
@endsection
