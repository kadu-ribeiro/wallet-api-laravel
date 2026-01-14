<?php

declare(strict_types=1);

namespace App\Application\Contracts\Wallet;

use Illuminate\Support\Collection;

interface GetTransactionHistoryUseCaseInterface
{
    public function execute(string $walletId, int $perPage = 50): Collection;
}
