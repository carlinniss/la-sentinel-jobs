<?php

declare(strict_types=1);

namespace Modules\User\App\Support;

final class FeaturedEmployerBrand
{
    public static function forName(string $name): ?array
    {
        $normalized = mb_strtolower(trim($name));

        if (str_contains($normalized, "st. john") || str_contains($normalized, 'st john')) {
            return [
                'key' => 'st-johns',
                'logo' => 'images/employers/st-johns-community-health.webp',
                'logo_alt' => "St. John's Community Health",
                'primary' => '#e65f45',
                'primary_dark' => '#bc422f',
                'soft' => '#fff3ef',
                'soft_border' => '#f4b5a7',
                'panel_background' => 'linear-gradient(135deg, #ffffff, #fff3ef)',
                'summary' => "St. John's Community Health is the founding sponsor of LA Sentinel Jobs, hiring for mission-driven careers that bring quality care to older adults and underserved communities.",
                'short_summary' => 'Community healthcare careers with purpose and local impact.',
                'detail_summary' => 'Mission-driven healthcare roles serving communities across Los Angeles and Southern California.',
                'profile_callout' => "St. John's is featured for healthcare careers that expand access to compassionate care and help reduce health disparities in underserved communities.",
                'proof_points' => ['Founding sponsor', 'Community health careers', 'Mission-driven care', 'Southern California impact'],
                'tags' => ['Healthcare careers', 'Community impact'],
                'careers_label' => "See all St. John's openings",
            ];
        }

        return null;
    }
}
