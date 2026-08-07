@php
    $siteName = $generalSettings['site_name'] ?? config('app.name', 'OpenClassify');
    $configuredLogoUrl = $generalSettings['site_logo_url'] ?? null;
    $siteLogoUrl = filled($configuredLogoUrl) ? $configuredLogoUrl : asset('images/la-sentinel/logo.webp');
    $pageTitle = trim($__env->yieldContent('title'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle !== '' ? $pageTitle.' - ' : '' }}{{ $siteName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="user-auth-page">
    <main class="user-auth-shell">
        <div class="user-auth-frame">
            <section class="user-auth-panel">
                <a href="{{ route('home') }}" class="user-auth-brand" aria-label="{{ $siteName }}">
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="user-auth-brand-image">
                    <span class="user-auth-brand-text">{{ $siteName }}</span>
                </a>

                <div class="user-auth-card">
                    @yield('content')
                </div>
            </section>
        </div>
    </main>
</body>
</html>
