<?php

declare(strict_types=1);

namespace Modules\User\App\Support;

final class FeaturedEmployerBrand
{
    public static function forName(string $name): ?array
    {
        $normalized = mb_strtolower(trim($name));

        if (str_contains($normalized, 'bmo')) {
            return [
                'key' => 'bmo',
                'logo' => 'images/employers/bmo.svg',
                'logo_alt' => 'BMO',
                'primary' => '#005eb8',
                'primary_dark' => '#004b93',
                'soft' => '#eef6ff',
                'soft_border' => '#b9d8f2',
                'panel_background' => 'linear-gradient(135deg, #061b35, #082f58)',
                'summary' => 'BMO is hiring through LA Sentinel Jobs with community banking roles and advancement pathways.',
                'short_summary' => 'Community banking roles and advancement pathways.',
                'detail_summary' => 'Community banking roles with local customer impact, training, benefits, and clear advancement paths.',
                'profile_callout' => 'BMO is featured here for roles that can connect Los Angeles candidates with banking, branch leadership, customer service, small business, and operations careers.',
                'proof_points' => ['Featured employer partner', 'Community banking careers', 'Customer-facing roles', 'Advancement pathways'],
                'tags' => ['Banking careers', 'Los Angeles hiring'],
                'careers_label' => 'BMO careers',
            ];
        }

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
                'summary' => "St. John's Community Health is hiring through LA Sentinel Jobs for mission-driven clinical, dental, behavioral health, and patient-support careers.",
                'short_summary' => 'Community healthcare careers with purpose and local impact.',
                'detail_summary' => 'Mission-driven healthcare roles serving communities across Los Angeles and Southern California.',
                'profile_callout' => "St. John's is featured for healthcare careers that expand access to compassionate care and help reduce health disparities in underserved communities.",
                'proof_points' => ['Featured employer partner', 'Community health careers', 'Mission-driven care', 'Southern California impact'],
                'tags' => ['Healthcare careers', 'Community impact'],
                'careers_label' => "St. John's careers",
            ];
        }

        return null;
    }
}
