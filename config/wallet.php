<?php

declare(strict_types=1);

return [
    'snapshot_frequency' => env('WALLET_SNAPSHOT_FREQUENCY', 100),

    'daily_limits' => [
        'withdrawal' => env('WALLET_DAILY_WITHDRAWAL_LIMIT', 500000), // R$ 5,000.00
        'transfer' => env('WALLET_DAILY_TRANSFER_LIMIT', 500000), // R$ 5,000.00
    ],

    'default_currency' => env('WALLET_DEFAULT_CURRENCY', 'BRL'),

    'notifications' => [
        'welcome_email' => env('WALLET_SEND_WELCOME_EMAIL', true),
        'transfer_received_email' => env('WALLET_SEND_TRANSFER_EMAIL', true),
    ],

    'webhook' => [
        'transfer_received_url' => env('WALLET_WEBHOOK_TRANSFER_URL'),
    ],
];
