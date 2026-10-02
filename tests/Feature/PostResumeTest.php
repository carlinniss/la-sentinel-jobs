<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\User\App\Models\Profile;
use Modules\User\App\Models\User;
use Modules\User\App\Notifications\ResumeSignupNotification;
use Tests\TestCase;

class PostResumeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['resume.upload_fee_cents' => 0]);
        Storage::fake('local');
        Notification::fake();
    }

    public function test_guest_sees_one_page_signup_form(): void
    {
        $this->get('/resume?src=TOS2026')
            ->assertOk()
            ->assertSee('Post your resume')
            ->assertSee('Create a password')
            ->assertSee('Welcome, Taste of Soul');

        $this->assertSame('tos2026', session('resume_signup_source'));
    }

    public function test_post_resume_alias_keeps_campaign_source(): void
    {
        $this->get('/post-resume?src=tos2026')->assertRedirect('/resume?src=tos2026');
    }

    public function test_guest_can_sign_up_and_post_resume_in_one_step(): void
    {
        $this->get('/resume?src=tos2026');

        $this->post('/resume', $this->signupPayload([
            'resume' => UploadedFile::fake()->create('jane.pdf', 200, 'application/pdf'),
            'resume_searchable' => '1',
        ]))->assertRedirect(route('resume.done'));

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();
        $profile = Profile::query()->where('user_id', $user->getKey())->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($profile->hasResume());
        $this->assertTrue($profile->resume_searchable);
        $this->assertTrue($profile->isDiscoverable());
        $this->assertSame('tos2026', $profile->signup_source);
        $this->assertSame('555-0100', $profile->phone);
        Storage::disk('local')->assertExists($profile->resume_path);
        Notification::assertSentTo($user, ResumeSignupNotification::class);

        $this->get(route('resume.done'))->assertOk()->assertSee("You're all set");
    }

    public function test_guest_can_sign_up_without_a_file_and_stays_private(): void
    {
        $this->post('/resume', $this->signupPayload())->assertRedirect(route('resume.done'));

        $profile = Profile::query()->firstOrFail();

        $this->assertFalse($profile->hasResume());
        $this->assertFalse($profile->resume_searchable);
        $this->assertFalse($profile->isDiscoverable());
        Notification::assertSentTo($profile->user, ResumeSignupNotification::class);

        $this->get(route('resume.done'))->assertOk()->assertSee('Upload my resume now');
    }

    public function test_existing_email_is_told_to_log_in(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $this->from('/resume')
            ->post('/resume', $this->signupPayload())
            ->assertRedirect('/resume')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_rejects_unsupported_file_types(): void
    {
        $this->from('/resume')
            ->post('/resume', $this->signupPayload([
                'resume' => UploadedFile::fake()->create('photo.exe', 20),
            ]))
            ->assertSessionHasErrors('resume');

        $this->assertGuest();
    }

    public function test_member_can_add_resume_later(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/resume')->assertOk()->assertSee('Post my resume');

        $this->actingAs($user)->post('/resume', [
            'resume' => UploadedFile::fake()->create('cv.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'resume_searchable' => '0',
        ])->assertRedirect(route('resume.done'));

        $profile = $user->fresh()->profile;

        $this->assertTrue($profile->hasResume());
        $this->assertFalse($profile->isDiscoverable());
        Notification::assertNothingSent();
    }

    public function test_member_without_file_must_choose_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/resume')
            ->post('/resume', ['resume_searchable' => '1'])
            ->assertSessionHasErrors('resume');
    }

    public function test_login_from_resume_page_returns_to_it(): void
    {
        config(['resume.jobs_board_enabled' => false]);

        User::factory()->create(['email' => 'pat@example.com', 'password' => 'Secret-password-123']);

        $this->post(route('login'), [
            'email' => 'pat@example.com',
            'password' => 'Secret-password-123',
            'redirect' => '/resume',
        ])->assertRedirect('/resume');
    }

    public function test_resume_only_mode_redirects_job_board_pages_to_the_form(): void
    {
        config(['resume.jobs_board_enabled' => false]);

        $this->get('/?src=tos2026')->assertRedirect('/resume?src=tos2026');
        $this->get('/listings')->assertRedirect('/resume');
        $this->get('/register')->assertRedirect('/resume');
        $this->get('/partners/inquiry')->assertRedirect('/resume');
        $this->post('/partners/inquiry')->assertNotFound();

        $this->get('/resume')->assertOk()->assertSee('Launching soon');
        $this->get('/terms')->assertOk()->assertSee('Terms of Use');
        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy');
        $this->get('/login')->assertOk();
    }

    public function test_resume_only_mode_keeps_member_resume_tools(): void
    {
        config(['resume.jobs_board_enabled' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/resume');
        $this->actingAs($user)->get('/panel/my-listings')->assertRedirect('/resume');
        $this->actingAs($user)->get('/panel/my-profile')->assertOk();
    }

    public function test_board_enabled_restores_job_pages(): void
    {
        config(['resume.jobs_board_enabled' => true]);

        $this->get('/resume')->assertOk()->assertDontSee('Launching soon');
        $this->get('/register')->assertOk();
    }

    private function signupPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => 'Secret-password-123',
            'phone' => '555-0100',
            'terms' => '1',
        ], $overrides);
    }
}
