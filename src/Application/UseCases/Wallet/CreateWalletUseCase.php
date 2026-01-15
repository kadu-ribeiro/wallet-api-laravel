<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\CreateWalletUseCaseInterface;
use App\Application\DTOs\Wallet\CreateWalletDTO;
use App\Domain\Wallet\Aggregates\WalletAggregate;
use Illuminate\Support\Str;

final readonly class CreateWalletUseCase implements CreateWalletUseCaseInterface
{
    public function execute(CreateWalletDTO $dto): string
    {
        $walletId = Str::orderedUuid()->toString();

        WalletAggregate::retrieve($walletId)
            ->createWallet($dto->userId)
            ->persist();

        return $walletId;
    }
}
