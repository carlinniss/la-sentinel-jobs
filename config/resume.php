<?php

declare(strict_types=1);

return [
    'jobs_board_enabled' => (bool) env('JOBS_BOARD_ENABLED', false),
    'contact_email' => (string) env('JOBS_CONTACT_EMAIL', 'carl@lasentinel.net'),
    'upload_fee_cents' => (int) env('RESUME_UPLOAD_FEE_CENTS', 0),
    'currency' => strtolower((string) env('RESUME_UPLOAD_CURRENCY', 'usd')),
    'stripe_secret' => (string) env('STRIPE_SECRET', ''),
    'stripe_webhook_secret' => (string) env('STRIPE_WEBHOOK_SECRET', ''),
];
