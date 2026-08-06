<?php

declare(strict_types=1);

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Apple\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->forceConfiguredUrlRoot();

        Gate::before(function ($user): ?bool {
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }

            return null;
        });

        View::addNamespace('app', resource_path('views'));

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('apple', Provider::class);
        });

        $availableLocales = config('app.available_locales', ['en']);
        $localeLabels = [
            'en' => 'English',
            'tr' => 'Turkish',
        ];

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) use ($availableLocales, $localeLabels): void {
            $switch
                ->locales($availableLocales)
                ->labels(collect($availableLocales)->mapWithKeys(
                    fn (string $locale) => [$locale => $localeLabels[$locale] ?? strtoupper($locale)]
                )->all())
                ->visible(insidePanels: count($availableLocales) > 1, outsidePanels: false);
        });
    }

    private function forceConfiguredUrlRoot(): void
    {
        $appUrl = trim((string) config('app.url', ''));
        $host = parse_url($appUrl, PHP_URL_HOST);
        $scheme = parse_url($appUrl, PHP_URL_SCHEME);

        if (! is_string($host) || $host === '' || str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            return;
        }

        URL::forceRootUrl($appUrl);

        if ($scheme === 'https') {
            URL::forceScheme('https');
        }
    }
}
