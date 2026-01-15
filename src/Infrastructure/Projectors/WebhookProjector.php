<?php

declare(strict_types=1);

namespace App\Infrastructure\Projectors;

use App\Domain\Wallet\Events\MoneyTransferredIn;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Throwable;

final class WebhookProjector extends Projector implements ShouldQueue
{
    private const REPLAY_THRESHOLD_MINUTES = 5;

    public string $queue = 'webhooks';

    public function onMoneyTransferredIn(MoneyTransferredIn $event, StoredEvent $storedEvent): void
    {
        Log::info('>>> WEBHOOK PROJECTOR CALLED <<<', [
            'wallet_id' => $event->walletId,
            'amount_cents' => $event->amountCents,
        ]);

        if ($this->isReplayEvent($storedEvent)) {
            Log::debug('Webhook skipped (replay event)', ['wallet_id' => $event->walletId]);
            return;
        }

        $webhookUrl = config('wallet.webhook.transfer_received_url');
        Log::info('Webhook URL', ['url' => $webhookUrl]);

        if (! $webhookUrl) {
            Log::warning('Webhook URL not configured');
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

    private function isReplayEvent(StoredEvent $storedEvent): bool
    {
        $eventCreatedAt = Carbon::parse($storedEvent->created_at);
        return $eventCreatedAt->lt(now()->subMinutes(self::REPLAY_THRESHOLD_MINUTES));
    }
}
