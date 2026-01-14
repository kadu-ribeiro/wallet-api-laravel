<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use Exception;

final class InsufficientBalanceException extends Exception
{
    public static function forWithdrawal(int $balanceCents, int $requestedCents): self
    {
        $available = number_format($balanceCents / 100, 2, '.', '');
        $requested = number_format($requestedCents / 100, 2, '.', '');

        return new self(
            "Insufficient balance. Available: {$available}, Requested: {$requested}"
        );
    }
}
