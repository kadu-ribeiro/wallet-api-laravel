<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\DTOs\WalletDTO;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\WalletId;
use App\Infrastructure\Persistence\Eloquent\Wallet;

final readonly class WalletRepository implements WalletRepositoryInterface
{
    public function findById(WalletId $id): ?WalletDTO
    {
        $wallet = Wallet::find($id->value);

        return $wallet ? $this->toDTO($wallet) : null;
    }

    public function findByUserId(UserId $userId): ?WalletDTO
    {
        $wallet = Wallet::where('user_id', $userId->value)->first();

        return $wallet ? $this->toDTO($wallet) : null;
    }

    public function exists(WalletId $id): bool
    {
        return Wallet::where('id', $id->value)->exists();
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
