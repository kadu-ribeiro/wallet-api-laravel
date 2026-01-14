<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Repositories;

interface TransactionRepositoryInterface
{
    public function idempotencyKeyExists(string $key): bool;
}
