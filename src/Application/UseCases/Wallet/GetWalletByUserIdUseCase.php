<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Domain\Wallet\DTOs\WalletDTO;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Queries\WalletQueryInterface;

final readonly class GetWalletByUserIdUseCase
{
    public function __construct(private WalletQueryInterface $walletQuery) {}

    public function execute(string $userId): WalletDTO
    {
        return $this->walletQuery->findByUserId($userId) ?? throw WalletNotFoundException::forUser($userId);
    }
}
