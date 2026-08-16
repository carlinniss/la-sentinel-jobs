<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\User\App\Support\FeaturedEmployerBrand;
use PHPUnit\Framework\TestCase;

class FeaturedEmployerBrandTest extends TestCase
{
    public function test_it_preserves_bmo_branding(): void
    {
        $brand = FeaturedEmployerBrand::forName('BMO Community Banking Careers');

        self::assertSame('bmo', $brand['key']);
        self::assertSame('images/employers/bmo.svg', $brand['logo']);
    }

    public function test_it_recognizes_st_johns_branding(): void
    {
        $brand = FeaturedEmployerBrand::forName("St. John's Community Health Careers");

        self::assertSame('st-johns', $brand['key']);
        self::assertSame('images/employers/st-johns-community-health.webp', $brand['logo']);
    }

    public function test_it_does_not_brand_unconfigured_employers(): void
    {
        self::assertNull(FeaturedEmployerBrand::forName('Neighborhood Employer'));
    }
}
