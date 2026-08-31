<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BroadstreetAdTemplateTest extends TestCase
{
    public function test_ad_template_uses_broadstreet_v2_zone_markup(): void
    {
        $template = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Site/resources/views/partials/broadstreet-ad.blade.php'
        );

        self::assertStringContainsString('<broadstreet-zone', $template);
        self::assertStringContainsString('zone-id="{{ $zoneId }}"', $template);
        self::assertStringContainsString('preview="true"', $template);
    }

    public function test_layout_loads_broadstreet_v2_script_asynchronously(): void
    {
        $layout = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Site/resources/views/layouts/app.blade.php'
        );

        self::assertStringContainsString('cdn.broadstreetads.com/init-2.min.js', $layout);
        self::assertStringContainsString('window.broadstreet.watch', $layout);
    }

    public function test_lasentinel_source_zones_are_configured(): void
    {
        $config = file_get_contents(dirname(__DIR__, 2).'/config/advertising.php');

        self::assertStringContainsString("env('BROADSTREET_NETWORK_ID', 10588)", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_BILLBOARD', 187227)", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_SOURCE_BODY_SECONDARY', 187229)", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_SOURCE_HEADER_LEFT', 187225)", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_SOURCE_HEADER_RIGHT', 187226)", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_LEADERBOARD', env('BROADSTREET_ZONE_BILLBOARD', 187227))", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_DISPLAY', env('BROADSTREET_ZONE_CUBE', 187228))", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_SKYSCRAPER', env('BROADSTREET_ZONE_SOURCE_HEADER_LEFT', 187225))", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_HALF_PAGE', env('BROADSTREET_ZONE_SOURCE_HEADER_RIGHT', 187226))", $config);
        self::assertStringContainsString("env('BROADSTREET_ZONE_CUBE', 187228)", $config);
    }

    public function test_public_home_and_search_pages_use_live_broadstreet_slots(): void
    {
        $home = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Site/resources/views/home.blade.php'
        );
        $index = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Listing/resources/views/partials/index-content.blade.php'
        );

        self::assertStringContainsString("@include('site::partials.broadstreet-ad'", $home);
        self::assertStringContainsString("@include('site::partials.broadstreet-ad'", $index);
        self::assertStringContainsString("'zone' => 'leaderboard'", $home);
        self::assertStringContainsString("'zone' => 'display'", $home);
        self::assertStringContainsString("'zone' => 'leaderboard'", $index);
        self::assertStringContainsString("'zone' => 'display'", $index);
        self::assertStringContainsString("'zone' => 'sidebar_skyscraper'", $index);
        self::assertStringContainsString("'zone' => 'sidebar_half_page'", $index);
        self::assertStringNotContainsString("@include('site::partials.google-ad-placeholder'", $home);
        self::assertStringNotContainsString("@include('site::partials.google-ad-placeholder'", $index);
    }

    public function test_broadstreet_slots_do_not_render_fake_ad_creative(): void
    {
        $template = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Site/resources/views/partials/broadstreet-ad.blade.php'
        );

        self::assertStringNotContainsString('LA Sentinel Jobs', $template);
        self::assertStringNotContainsString('Advertising space', $template);
        self::assertStringNotContainsString('Amazing Cube', $template);
    }

    public function test_job_detail_uses_the_lasentinel_google_tile_four_slot(): void
    {
        $layout = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Site/resources/views/layouts/app.blade.php'
        );
        $tile = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Site/resources/views/partials/google-publisher-tile.blade.php'
        );
        $detail = file_get_contents(
            dirname(__DIR__, 2).'/Modules/Listing/resources/views/themes/default/show.blade.php'
        );

        self::assertStringContainsString('securepubads.g.doubleclick.net/tag/js/gpt.js', $layout);
        self::assertStringContainsString("defineSlot('/17020487/Tile_4', [300, 250], 'div-gpt-ad-1778596674801-0')", $layout);
        self::assertStringContainsString("googletag.display('div-gpt-ad-1778596674801-0')", $tile);
        self::assertStringContainsString("@include('site::partials.google-publisher-tile'", $detail);
        self::assertStringNotContainsString("'zone' => 'cube'", $detail);
    }
}
