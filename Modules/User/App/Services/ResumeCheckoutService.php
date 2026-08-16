<?php

declare(strict_types=1);

namespace Modules\User\App\Services;

use Modules\User\App\Models\Profile;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

final class ResumeCheckoutService
{
    public function configured(): bool
    {
        return trim((string) config('resume.stripe_secret')) !== '';
    }

    public function create(Profile $profile): Session
    {
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'client_reference_id' => (string) $profile->getKey(),
            'customer_email' => $profile->user?->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => config('resume.currency'),
                    'unit_amount' => $profile->resume_fee_cents,
                    'product_data' => [
                        'name' => 'LA Sentinel Jobs Resume Bank Submission',
                        'description' => 'Private resume hosting with optional authorized-employer discovery.',
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'purpose' => 'resume_upload',
                'profile_id' => (string) $profile->getKey(),
            ],
            'success_url' => route('resume.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('panel.profile.edit').'?resume_payment=cancelled',
        ]);

        $profile->forceFill(['resume_checkout_session_id' => $session->id])->save();

        return $session;
    }

    public function fulfill(string $sessionId): bool
    {
        $session = $this->client()->checkout->sessions->retrieve($sessionId, []);

        if ($session->payment_status !== 'paid' || ($session->metadata->purpose ?? null) !== 'resume_upload') {
            return false;
        }

        $profile = Profile::query()->find((int) ($session->metadata->profile_id ?? 0));

        if (! $profile || $profile->resume_checkout_session_id !== $session->id) {
            return false;
        }

        if ((int) $session->amount_total !== (int) $profile->resume_fee_cents) {
            return false;
        }

        if ($profile->resume_paid_at === null) {
            $profile->forceFill(['resume_paid_at' => now()])->save();
        }

        return true;
    }

    private function client(): StripeClient
    {
        $secret = trim((string) config('resume.stripe_secret'));

        if ($secret === '') {
            throw new RuntimeException('Stripe resume payments are not configured.');
        }

        return new StripeClient($secret);
    }
}
