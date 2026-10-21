@extends('app::layouts.app')
@section('content')
@php
    $title = trim((string) ($listing->title ?? ''));
    $displayTitle = ($title !== '' && preg_match('/[\pL\pN]/u', $title)) ? $title : 'Untitled listing';

    $city = trim((string) ($listing->city ?? ''));
    $country = trim((string) ($listing->country ?? ''));
    $location = implode(', ', array_filter([$city, $country], fn ($value) => $value !== ''));

    $description = trim((string) ($listing->description ?? ''));
    $displayDescription = ($description !== '' && preg_match('/[\pL\pN]/u', $description))
        ? $description
        : 'No description provided.';

    $hasPrice = !is_null($listing->price);
    $priceValue = $hasPrice ? (float) $listing->price : null;
    $formatCompensation = static function ($amount, $currency = 'USD'): string {
        if (is_null($amount) || (float) $amount <= 0) {
            return __('listing::messages.price_on_request');
        }

        $amount = (float) $amount;
        $prefix = $currency === 'USD' ? '$' : '';
        $suffix = $amount < 1000 ? '/hr' : '/yr';

        return $prefix.number_format($amount, 0).$suffix;
    };
    $galleryImages = collect($gallery ?? []);
    $mainImage = $galleryImages->first();
    $breadcrumbs = collect($breadcrumbCategories ?? []);
    $sentinelUrl = 'https://lasentinel.net';
    $detailEmployerName = trim((string) ($listing->user?->name ?? ''));
    $detailEmployerBrand = \Modules\User\App\Support\FeaturedEmployerBrand::forName($detailEmployerName);
@endphp
<div class="job-page">
    @if($breadcrumbs->isNotEmpty())
    <nav class="job-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('listings.index') }}">{{ __('site::messages.listings') }}</a>
        @foreach($breadcrumbs as $crumb)
        <span aria-hidden="true">/</span>
        <a href="{{ route('listings.index', ['category' => $crumb->id]) }}">{{ $crumb->name }}</a>
        @endforeach
    </nav>
    @endif

    <header class="job-hero">
        <div class="job-hero__main">
            @if($listing->user)
            <a href="{{ route('employers.show', $listing->user) }}" class="job-hero__employer">
                @if($detailEmployerBrand && ! empty($detailEmployerBrand['logo']))
                <span class="job-hero__employer-logo"><img src="{{ asset($detailEmployerBrand['logo']) }}" alt=""></span>
                @endif
                <span>
                    <strong>{{ $detailEmployerName }}</strong>
                    @if(($detailEmployerBrand['key'] ?? null) === 'st-johns')
                    <small>Founding sponsor of LA Sentinel Jobs</small>
                    @endif
                </span>
            </a>
            @endif
            <h1 class="job-hero__title">{{ $displayTitle }}</h1>
            <dl class="job-hero__facts">
                @if($hasPrice && $priceValue > 0)
                <div>
                    <dt>Pay</dt>
                    <dd>{{ $formatCompensation($priceValue, $listing->currency ?? 'USD') }}</dd>
                </div>
                @endif
                @if($listing->category)
                <div>
                    <dt>Category</dt>
                    <dd>{{ $listing->category->name }}</dd>
                </div>
                @endif
                <div>
                    <dt>Location</dt>
                    <dd>{{ $location !== '' ? $location : __('listing::messages.location_not_specified') }}</dd>
                </div>
                <div>
                    <dt>Posted</dt>
                    <dd>{{ $listing->created_at?->diffForHumans() ?? 'Recently' }}</dd>
                </div>
            </dl>
        </div>

        <div class="job-hero__actions">
            @if($listing->acceptsEmailApplications())
            <a href="{{ route('listings.apply', $listing) }}" class="job-apply-button" rel="nofollow">Apply for this job</a>
            <p class="job-apply-help">Opens your email with the job title filled in. Attach your resume and send.</p>
            @endif

            @if(config('resume.chat_enabled') && $listing->user)
                @auth
                    @if((int) $listing->user->id !== (int) auth()->id())
                        @if($detailConversation)
                        <a href="{{ route('panel.inbox.index', ['conversation' => $detailConversation->id]) }}" class="job-secondary-button">{{ __('listing::messages.open_chat') }}</a>
                        @else
                        <form method="POST" action="{{ route('conversations.start', $listing) }}">
                            @csrf
                            <button type="submit" class="job-secondary-button">{{ __('listing::messages.message_seller') }}</button>
                        </form>
                        @endif
                    @endif
                @else
                <a href="{{ route('login') }}" class="job-secondary-button">{{ __('listing::messages.log_in_to_contact_seller') }}</a>
                @endauth
            @endif

            @auth
            <form method="POST" action="{{ route('favorites.listings.toggle', $listing) }}">
                @csrf
                <button type="submit" class="job-save-button {{ $isListingFavorited ? 'is-active' : '' }}">
                    {{ $isListingFavorited ? __('listing::messages.saved') : __('listing::messages.save_listing') }}
                </button>
            </form>
            @endauth
        </div>
    </header>

    <div class="job-layout">
        <div class="job-layout__main">
            @if($mainImage)
            <figure class="job-photo">
                @include('listing::partials.responsive-image', [
                    'image' => $mainImage['gallery'] ?? null,
                    'alt' => $displayTitle,
                    'class' => 'w-full h-full object-cover',
                    'loading' => 'eager',
                    'fetchpriority' => 'high',
                ])
            </figure>
            @endif

            @if($galleryImages->count() > 1)
            <div class="job-thumbs">
                @foreach($galleryImages->skip(1)->take(5) as $image)
                <div>
                    @include('listing::partials.responsive-image', [
                        'image' => $image['thumb'] ?? null,
                        'alt' => $displayTitle,
                        'class' => 'w-full h-full object-cover',
                    ])
                </div>
                @endforeach
            </div>
            @endif

            <section class="job-section">
                <h2 class="job-description-title">About the job</h2>
                @php
                    $descriptionBlocks = \Modules\Listing\Support\ListingDescriptionBlocks::fromText($displayDescription);
                @endphp
                <div class="job-description">
                    @foreach($descriptionBlocks as $block)
                        @switch($block['type'])
                            @case('meta')
                                <ul class="job-description-meta">
                                    @foreach($block['items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                @break
                            @case('note')
                                <p class="job-description-note">{{ $block['text'] }}</p>
                                @break
                            @case('heading')
                                <h3>{{ $block['text'] }}</h3>
                                @break
                            @case('list')
                                <ul class="job-description-list">
                                    @foreach($block['items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                @break
                            @default
                                <p>{{ $block['text'] }}</p>
                        @endswitch
                    @endforeach
                </div>

                @if($listing->acceptsEmailApplications())
                <div class="job-section__apply">
                    <a href="{{ route('listings.apply', $listing) }}" class="job-apply-button" rel="nofollow">Apply for this job</a>
                </div>
                @endif
            </section>

            @if(($listingVideos ?? collect())->isNotEmpty())
            <section class="job-section">
                <h2 class="job-description-title">{{ __('listing::messages.videos') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($listingVideos as $video)
                    <div class="rounded-xl border border-[var(--oc-border)] p-3">
                        <video class="w-full rounded-lg bg-black" controls preload="metadata" src="{{ $video->playableUrl() }}"></video>
                        <p class="mt-2 text-sm font-medium text-[var(--oc-text)]">{{ $video->titleLabel() }}</p>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            @if(($presentableCustomFields ?? []) !== [])
            <section class="job-section">
                <h2 class="job-description-title">{{ __('listing::messages.listing_details') }}</h2>
                <dl class="job-details-grid">
                    @foreach($presentableCustomFields as $field)
                    <div>
                        <dt>{{ $field['label'] }}</dt>
                        <dd>{{ $field['value'] }}</dd>
                    </div>
                    @endforeach
                </dl>
            </section>
            @endif
        </div>

        <aside class="job-layout__aside">
            @if($listing->user)
            <section class="job-employer-card">
                <h2>About the employer</h2>
                @if($detailEmployerBrand && ! empty($detailEmployerBrand['logo']))
                <div class="job-employer-card__logo"><img src="{{ asset($detailEmployerBrand['logo']) }}" alt="{{ $detailEmployerBrand['logo_alt'] ?? $detailEmployerName }}"></div>
                @endif
                <p class="job-employer-card__name">{{ $detailEmployerName }}</p>
                @php
                    $employerBio = trim((string) ($listing->user->profile?->bio ?? ''));
                    $employerWebsite = trim((string) ($listing->user->profile?->website ?? ''));
                @endphp
                @if($employerBio !== '')
                <p class="job-employer-card__bio">{{ \Illuminate\Support\Str::limit($employerBio, 260) }}</p>
                @endif
                <div class="job-employer-card__links">
                    <a href="{{ route('employers.show', $listing->user) }}">Employer profile</a>
                    <a href="{{ route('listings.index', ['user' => $listing->user->getKey()]) }}">All jobs from this employer</a>
                    @if($employerWebsite !== '')
                    <a href="{{ $employerWebsite }}" target="_blank" rel="noopener">{{ $detailEmployerBrand['careers_label'] ?? 'Website' }}</a>
                    @endif
                </div>
            </section>
            @endif

            <div class="job-layout__ad">
                @include('site::partials.google-publisher-tile', [
                    'placement' => 'job-detail-sidebar',
                ])
            </div>
        </aside>
    </div>

    @if(($relatedListings ?? collect())->isNotEmpty())
    <section class="job-related">
        <h2 class="job-description-title">More jobs like this</h2>
        <div class="job-related__grid">
            @foreach($relatedListings as $related)
            @php
                $relatedImage = $related->primaryImageData('card');
                $relatedDetail = ($related->price && (float) $related->price > 0)
                    ? $formatCompensation($related->price, $related->currency ?? 'USD')
                    : ($related->category?->name ?? '');
            @endphp
            <a href="{{ route('listings.show', $related) }}" class="job-related__card">
                <div class="job-related__photo">
                    @if($relatedImage)
                    @include('listing::partials.responsive-image', [
                        'image' => $relatedImage,
                        'alt' => $related->title,
                        'class' => 'w-full h-full object-cover',
                    ])
                    @endif
                </div>
                <div class="job-related__body">
                    <p class="job-related__title">{{ $related->title }}</p>
                    <p class="job-related__meta">{{ $related->user?->name }}</p>
                    @if($relatedDetail !== '')
                    <p class="job-related__meta">{{ $relatedDetail }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
