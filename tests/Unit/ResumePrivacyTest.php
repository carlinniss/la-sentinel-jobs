<?php

declare(strict_types=1);

namespace Tests\Unit;

use Modules\User\App\Models\Profile;
use PHPUnit\Framework\TestCase;

class ResumePrivacyTest extends TestCase
{
    public function test_resume_is_private_by_default(): void
    {
        $profile = new Profile([
            'resume_path' => 'resumes/1/example.pdf',
        ]);

        self::assertFalse($profile->isDiscoverable());
    }

    public function test_candidate_consent_and_payment_make_resume_discoverable(): void
    {
        $profile = new Profile([
            'resume_path' => 'resumes/1/example.pdf',
            'resume_searchable' => true,
            'resume_fee_cents' => 500,
            'resume_paid_at' => '2026-08-16 00:00:00',
        ]);

        self::assertTrue($profile->isDiscoverable());
    }

    public function test_unpaid_resume_is_not_discoverable(): void
    {
        $profile = new Profile([
            'resume_path' => 'resumes/1/example.pdf',
            'resume_searchable' => true,
            'resume_fee_cents' => 500,
        ]);

        self::assertFalse($profile->isDiscoverable());
    }

    public function test_only_verified_entitled_employers_can_browse(): void
    {
        $profile = new Profile([
            'is_verified' => true,
            'resume_access_enabled' => true,
        ]);

        self::assertTrue($profile->canBrowseResumes());
    }
}
