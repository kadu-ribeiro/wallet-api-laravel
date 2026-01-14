<?php

declare(strict_types=1);

namespace App\Application\Contracts\Wallet;

use App\Domain\Wallet\DTOs\WalletDTO;

interface GetWalletBalanceUseCaseInterface
{
    public function execute(string $walletId): WalletDTO;
}
