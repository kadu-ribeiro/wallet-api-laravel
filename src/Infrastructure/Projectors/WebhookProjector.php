<?php

declare(strict_types=1);

namespace App\Infrastructure\Projectors;

use App\Domain\Wallet\Events\MoneyTransferredIn;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Throwable;

final class WebhookProjector extends Projector
{
    public function onMoneyTransferredIn(MoneyTransferredIn $event): void
    {
        $webhookUrl = config('wallet.webhook.transfer_received_url');

        if (! $webhookUrl) {
            return;
        }

        try {
            $response = Http::timeout(10)
                ->retry(3, 100)
                ->post($webhookUrl, [
                    'event' => 'transfer.received',
                    'wallet_id' => $event->walletId,
                    'amount_cents' => $event->amountCents,
                    'sender_email' => $event->senderEmail,
                    'transfer_id' => $event->transferId,
                    'timestamp' => now()->toIso8601String(),
                ]);

            if ($response->failed()) {
                Log::warning('Webhook failed', [
                    'status' => $response->status(),
                    'wallet_id' => $event->walletId,
                ]);
            } else {
                Log::info('Webhook sent successfully', ['wallet_id' => $event->walletId]);
            }
        } catch (Throwable $e) {
            Log::error('Webhook exception', [
                'error' => $e->getMessage(),
                'wallet_id' => $event->walletId,
            ]);
        }
    }
}
