<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Queries;

use App\Domain\Wallet\DTOs\WalletDTO;
use App\Domain\Wallet\Queries\WalletQueryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\Wallet;

final readonly class WalletQuery implements WalletQueryInterface
{
    public function findById(string $id): ?WalletDTO
    {
        $wallet = Wallet::find($id);

        return $wallet ? $this->toDTO($wallet) : null;
    }

    public function findByUserId(string $userId): ?WalletDTO
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        return $wallet ? $this->toDTO($wallet) : null;
    }

    private function toDTO(Wallet $wallet): WalletDTO
    {
        return WalletDTO::fromPrimitives(
            id: $wallet->id,
            userId: $wallet->user_id,
            balanceCents: $wallet->balance_cents,
            currency: $wallet->currency,
            createdAt: $wallet->created_at->toIso8601String()
        );
    }
}
