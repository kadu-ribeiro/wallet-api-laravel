<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Queries;

use App\Domain\Wallet\DTOs\WalletDTO;

interface WalletQueryInterface
{
    public function findById(string $id): ?WalletDTO;

    public function findByUserId(string $userId): ?WalletDTO;
}
