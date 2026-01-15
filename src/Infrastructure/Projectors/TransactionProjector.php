<?php

declare(strict_types=1);

namespace App\Infrastructure\Projectors;

use App\Domain\Wallet\Events\MoneyDeposited;
use App\Domain\Wallet\Events\MoneyTransferredIn;
use App\Domain\Wallet\Events\MoneyTransferredOut;
use App\Domain\Wallet\Events\MoneyWithdrawn;
use App\Infrastructure\Persistence\Eloquent\Transaction;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

final class TransactionProjector extends Projector
{
    public function onMoneyDeposited(MoneyDeposited $event): void
    {
        Transaction::create([
            'wallet_id' => $event->walletId,
            'type' => 'deposit',
            'amount_cents' => $event->amountCents,
            'balance_after_cents' => $event->balanceAfterCents,
            'idempotency_key' => $event->idempotencyKey,
            'metadata' => $event->metadata,
            'created_at' => now(),
        ]);
    }

    public function onMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        Transaction::create([
            'wallet_id' => $event->walletId,
            'type' => 'withdrawal',
            'amount_cents' => $event->amountCents,
            'balance_after_cents' => $event->balanceAfterCents,
            'idempotency_key' => $event->idempotencyKey,
            'metadata' => $event->metadata,
            'created_at' => now(),
        ]);
    }

    public function onMoneyTransferredOut(MoneyTransferredOut $event): void
    {
        Transaction::create([
            'wallet_id' => $event->walletId,
            'type' => 'transfer_out',
            'amount_cents' => $event->amountCents,
            'balance_after_cents' => $event->balanceAfterCents,
            'related_user_email' => $event->recipientEmail,
            'idempotency_key' => $event->transferId,
            'metadata' => $event->metadata,
            'created_at' => now(),
        ]);
    }

    public function onMoneyTransferredIn(MoneyTransferredIn $event): void
    {
        $relatedTx = Transaction::where('idempotency_key', $event->transferId)
            ->where('type', 'transfer_out')
            ->first();

        $transaction = Transaction::create([
            'wallet_id' => $event->walletId,
            'type' => 'transfer_in',
            'amount_cents' => $event->amountCents,
            'balance_after_cents' => $event->balanceAfterCents,
            'related_user_email' => $event->senderEmail,
            'related_transaction_id' => $relatedTx?->id,
            'idempotency_key' => $event->transferId.'_in',
            'metadata' => $event->metadata,
            'created_at' => now(),
        ]);

        if ($relatedTx) {
            $relatedTx->update(['related_transaction_id' => $transaction->id]);
        }
    }
}
