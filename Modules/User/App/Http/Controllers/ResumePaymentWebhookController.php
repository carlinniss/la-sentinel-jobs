<?php

declare(strict_types=1);

namespace Modules\User\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\App\Services\ResumeCheckoutService;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class ResumePaymentWebhookController extends Controller
{
    public function __invoke(Request $request, ResumeCheckoutService $checkout): Response
    {
        $secret = trim((string) config('resume.stripe_webhook_secret'));

        abort_if($secret === '', 503, 'Stripe webhook is not configured.');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid webhook signature.', 400);
        }

        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $checkout->fulfill((string) $event->data->object->id);
        }

        return response('Accepted.', 200);
    }
}
