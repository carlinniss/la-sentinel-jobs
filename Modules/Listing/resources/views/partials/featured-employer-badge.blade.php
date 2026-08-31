@php
    $variant = $variant ?? 'card';
    $employerName = trim((string) ($employerName ?? 'Featured Employer'));
    $profile = $profile ?? null;
    $bio = trim((string) ($profile?->bio ?? ''));
    $website = trim((string) ($profile?->website ?? ''));
    $employer = $employer ?? null;
    $linkEmployerProfile = (bool) ($linkEmployerProfile ?? true);
    $profileUrl = ($linkEmployerProfile && $employer) ? route('employers.show', $employer) : null;
    $brand = \Modules\User\App\Support\FeaturedEmployerBrand::forName($employerName);
    $themeClass = $brand ? 'featured-employer-badge--'.$brand['key'] : 'featured-employer-badge--community';
    $eyebrow = ($brand['key'] ?? null) === 'st-johns' ? 'Healthcare launch partner' : 'Featured employer partner';
    $summary = $brand['summary'] ?? 'This employer is hiring through LA Sentinel Jobs with local workforce opportunities.';
    $detailSummary = $bio !== '' ? \Illuminate\Support\Str::limit($bio, 230) : ($brand['detail_summary'] ?? $summary);
    $tags = $brand['tags'] ?? ['Local hiring', 'Community impact'];
    $careersLabel = $brand['careers_label'] ?? 'Careers site';
    $logoPath = $brand['logo'] ?? null;
    $logoAlt = $brand['logo_alt'] ?? $employerName;
@endphp

@if ($variant === 'feature-strip')
    <div class="featured-employer-badge {{ $themeClass }} featured-employer-badge--strip">
        <div class="featured-employer-badge__strip-inner">
            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="featured-employer-badge__identity" aria-label="View {{ $employerName }} employer profile">
                    <span class="featured-employer-badge__logo">
                        @if($logoPath)
                            <img src="{{ asset($logoPath) }}" alt="{{ $logoAlt }}">
                        @else
                            <span>{{ mb_strtoupper(mb_substr($employerName, 0, 1)) }}</span>
                        @endif
                    </span>
                    <span class="featured-employer-badge__copy">
                        <span class="featured-employer-badge__eyebrow">{{ $eyebrow }}</span>
                        <span class="featured-employer-badge__name">{{ $employerName }}</span>
                        <span class="featured-employer-badge__summary">{{ $summary }}</span>
                    </span>
                </a>
            @else
                <div class="featured-employer-badge__identity">
                    <span class="featured-employer-badge__logo">
                        @if($logoPath)
                            <img src="{{ asset($logoPath) }}" alt="{{ $logoAlt }}">
                        @else
                            <span>{{ mb_strtoupper(mb_substr($employerName, 0, 1)) }}</span>
                        @endif
                    </span>
                    <span class="featured-employer-badge__copy">
                        <span class="featured-employer-badge__eyebrow">{{ $eyebrow }}</span>
                        <span class="featured-employer-badge__name">{{ $employerName }}</span>
                        <span class="featured-employer-badge__summary">{{ $summary }}</span>
                    </span>
                </div>
            @endif

            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="featured-employer-badge__button">
                    View featured profile
                </a>
            @endif
        </div>
    </div>
@elseif ($variant === 'detail')
    <div class="featured-employer-badge {{ $themeClass }} featured-employer-badge--detail">
        <div class="featured-employer-badge__logo featured-employer-badge__logo--large">
            @if($logoPath)
                <img src="{{ asset($logoPath) }}" alt="{{ $logoAlt }}">
            @else
                <span>{{ mb_strtoupper(mb_substr($employerName, 0, 1)) }}</span>
            @endif
        </div>
        <div class="featured-employer-badge__copy">
            <p class="featured-employer-badge__eyebrow">{{ $eyebrow }}</p>
            <h3 class="featured-employer-badge__detail-name">{{ $employerName }}</h3>
            <p class="featured-employer-badge__detail-summary">{{ $detailSummary }}</p>
        </div>
        <div class="featured-employer-badge__tags">
            @foreach($tags as $tag)
                <span>{{ $tag }}</span>
            @endforeach
            @if ($profileUrl)
                <a href="{{ $profileUrl }}">Employer profile</a>
            @endif
            @if ($website !== '')
                <a href="{{ $website }}" target="_blank" rel="noopener">{{ $careersLabel }}</a>
            @endif
        </div>
    </div>
@else
    @if ($profileUrl)
        <a href="{{ $profileUrl }}" aria-label="View {{ $employerName }} employer profile" class="featured-employer-badge {{ $themeClass }} featured-employer-badge--card">
            <span class="featured-employer-badge__logo featured-employer-badge__logo--small">
                @if($logoPath)
                    <img src="{{ asset($logoPath) }}" alt="{{ $logoAlt }}">
                @else
                    <span>{{ mb_strtoupper(mb_substr($employerName, 0, 1)) }}</span>
                @endif
            </span>
            <span class="featured-employer-badge__copy">
                <span class="featured-employer-badge__eyebrow">Featured employer</span>
                <span class="featured-employer-badge__compact-name">{{ $employerName }}</span>
            </span>
        </a>
    @else
        <div class="featured-employer-badge {{ $themeClass }} featured-employer-badge--card">
            <span class="featured-employer-badge__logo featured-employer-badge__logo--small">
                @if($logoPath)
                    <img src="{{ asset($logoPath) }}" alt="{{ $logoAlt }}">
                @else
                    <span>{{ mb_strtoupper(mb_substr($employerName, 0, 1)) }}</span>
                @endif
            </span>
            <span class="featured-employer-badge__copy">
                <span class="featured-employer-badge__eyebrow">Featured employer</span>
                <span class="featured-employer-badge__compact-name">{{ $employerName }}</span>
            </span>
        </div>
    @endif
@endif
