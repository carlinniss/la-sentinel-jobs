<?php

declare(strict_types=1);

namespace Modules\Site\App\Support;

use Illuminate\Support\Arr;

final class HomeSlideDefaults
{
    public static function defaults(): array
    {
        return [
            [
                'badge' => 'LA Sentinel Community Jobs',
                'title' => 'Jobs, hiring events, and career pathways for Black Los Angeles.',
                'subtitle' => 'A trusted community jobs platform connecting Sentinel readers with employers, training partners, nonprofits, schools, healthcare teams, and public agencies.',
                'primary_button_text' => 'Browse Jobs',
                'secondary_button_text' => 'Post a Job',
                'image_path' => null,
            ],
            [
                'badge' => 'Community Hiring Network',
                'title' => 'Find roles across healthcare, education, skilled trades, banking, media, and public service.',
                'subtitle' => 'Applicants can save searches, message hiring teams, and compare pay, schedule, location, benefits, and advancement pathways in one clean flow.',
                'primary_button_text' => 'See Categories',
                'secondary_button_text' => 'Create Profile',
                'image_path' => null,
            ],
            [
                'badge' => 'Demo Ready',
                'title' => 'Show employers a jobs board that feels built for outreach, trust, and local impact.',
                'subtitle' => 'Seeded listings, employer profiles, saved searches, inbox conversations, and admin workflows are ready for a live walkthrough.',
                'primary_button_text' => 'View Jobs',
                'secondary_button_text' => 'Open Dashboard',
                'image_path' => null,
            ],
        ];
    }

    public static function normalize(mixed $slides): array
    {
        $defaults = self::defaults();
        $source = is_array($slides) ? $slides : [];

        $normalized = collect($source)
            ->filter(fn ($slide): bool => is_array($slide))
            ->values()
            ->map(function (array $slide, int $index) use ($defaults): ?array {
                $fallback = $defaults[$index] ?? $defaults[array_key_last($defaults)];
                $badge = trim((string) ($slide['badge'] ?? ''));
                $title = trim((string) ($slide['title'] ?? ''));
                $subtitle = trim((string) ($slide['subtitle'] ?? ''));
                $primaryButtonText = trim((string) ($slide['primary_button_text'] ?? ''));
                $secondaryButtonText = trim((string) ($slide['secondary_button_text'] ?? ''));
                $imagePath = self::normalizeImagePath($slide['image_path'] ?? null);

                if ($title === '') {
                    return null;
                }

                return [
                    'badge' => $badge !== '' ? $badge : $fallback['badge'],
                    'title' => $title,
                    'subtitle' => $subtitle !== '' ? $subtitle : $fallback['subtitle'],
                    'primary_button_text' => $primaryButtonText !== '' ? $primaryButtonText : $fallback['primary_button_text'],
                    'secondary_button_text' => $secondaryButtonText !== '' ? $secondaryButtonText : $fallback['secondary_button_text'],
                    'image_path' => $imagePath !== '' ? $imagePath : ($fallback['image_path'] ?? null),
                ];
            })
            ->filter(fn ($slide): bool => is_array($slide))
            ->values();

        return $normalized
            ->concat(collect($defaults)->slice($normalized->count()))
            ->take(count($defaults))
            ->values()
            ->all();
    }

    private static function normalizeImagePath(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            $firstValue = Arr::first($value, fn ($item): bool => is_string($item) && trim($item) !== '');

            return is_string($firstValue) ? trim($firstValue) : '';
        }

        return '';
    }
}
