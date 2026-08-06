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
        $this->forceCodespacesUrlRoot();

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

    private function forceCodespacesUrlRoot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $codespacesHost = $this->codespacesHost();

        if (is_string($codespacesHost)) {
            URL::forceRootUrl('https://'.$codespacesHost);
            URL::forceScheme('https');

            return;
        }

        return;
    }

    private function codespacesHost(): ?string
    {
        $hosts = [
            $this->firstForwardedHeaderValue((string) request()->headers->get('x-forwarded-host', '')),
            request()->getHost(),
        ];

        foreach ($hosts as $host) {
            $host = strtolower(trim($host));

            if ($host === '') {
                continue;
            }

            $hostWithoutPort = explode(':', $host)[0] ?? $host;

            if (str_ends_with($hostWithoutPort, '.github.dev') || str_ends_with($hostWithoutPort, '.app.github.dev')) {
                return $host;
            }
        }

        return null;
    }

    private function firstForwardedHeaderValue(string $value): string
    {
        return trim(explode(',', $value)[0] ?? '');
    }
}
