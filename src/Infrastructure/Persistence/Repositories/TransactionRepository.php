<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Wallet\Repositories\TransactionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Transaction;

final readonly class TransactionRepository implements TransactionRepositoryInterface
{
    public function idempotencyKeyExists(string $key): bool
    {
        return Transaction::where('idempotency_key', $key)->exists();
    }
}
