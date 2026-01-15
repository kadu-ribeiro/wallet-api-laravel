<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Queries;

use App\Domain\Wallet\DTOs\TransactionDTO;
use App\Domain\Wallet\Queries\TransactionQueryInterface;
use App\Infrastructure\Persistence\Eloquent\Transaction;
use Illuminate\Support\Collection;

final readonly class TransactionQuery implements TransactionQueryInterface
{
    public function findByWalletId(string $walletId, int $perPage = 50): Collection
    {
        return Transaction::where('wallet_id', $walletId)
            ->orderBy('created_at', 'desc')
            ->limit($perPage)
            ->get()
            ->map(fn (Transaction $tx) => TransactionDTO::fromPrimitives(
                id: (int) $tx->id,
                walletId: $tx->wallet_id,
                type: $tx->type,
                amountCents: $tx->amount_cents,
                balanceAfterCents: $tx->balance_after_cents,
                currency: $tx->currency ?? 'BRL',
                relatedUserEmail: $tx->related_user_email,
                relatedTransactionId: $tx->related_transaction_id ? (int) $tx->related_transaction_id : null,
                metadata: $tx->metadata ?? [],
                createdAt: $tx->created_at->toIso8601String()
            ));
    }
}
