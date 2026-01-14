<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use DomainException;

final class WalletNotFoundException extends DomainException
{
    public static function withId(string $walletId): self
    {
        return new self("Wallet with ID '{$walletId}' not found");
    }

    public static function forUser(string $userId): self
    {
        return new self("Wallet for User ID '{$userId}' not found");
    }
}
