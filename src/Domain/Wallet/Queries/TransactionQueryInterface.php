<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Queries;

use App\Domain\Wallet\DTOs\TransactionDTO;
use Illuminate\Support\Collection;

interface TransactionQueryInterface
{
    /**
     * @return Collection<int, TransactionDTO>
     */
    public function findByWalletId(string $walletId, int $perPage = 50): Collection;
}
