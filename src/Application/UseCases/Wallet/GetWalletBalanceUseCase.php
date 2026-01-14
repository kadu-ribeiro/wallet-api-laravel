<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\GetWalletBalanceUseCaseInterface;
use App\Domain\Wallet\DTOs\WalletDTO;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Queries\WalletQueryInterface;

final readonly class GetWalletBalanceUseCase implements GetWalletBalanceUseCaseInterface
{
    public function __construct(
        private WalletQueryInterface $walletQuery
    ) {}

    public function execute(string $walletId): WalletDTO
    {
        return $this->walletQuery->findById($walletId)
            ?? throw WalletNotFoundException::withId($walletId);
    }
}
