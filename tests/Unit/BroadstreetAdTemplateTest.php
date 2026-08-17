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
