<?php

declare(strict_types=1);

return [
    'upload_fee_cents' => (int) env('RESUME_UPLOAD_FEE_CENTS', 0),
    'currency' => strtolower((string) env('RESUME_UPLOAD_CURRENCY', 'usd')),
    'stripe_secret' => (string) env('STRIPE_SECRET', ''),
    'stripe_webhook_secret' => (string) env('STRIPE_WEBHOOK_SECRET', ''),
];
