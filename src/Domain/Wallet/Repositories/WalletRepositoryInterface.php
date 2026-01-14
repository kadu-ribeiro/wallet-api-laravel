<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Repositories;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\DTOs\WalletDTO;
use App\Domain\Wallet\ValueObjects\WalletId;

interface WalletRepositoryInterface
{
    public function findById(WalletId $id): ?WalletDTO;

    public function findByUserId(UserId $userId): ?WalletDTO;

    public function exists(WalletId $id): bool;
}
