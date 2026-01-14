<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use DomainException;

final class TransferAlreadyProcessedException extends DomainException
{
    public static function withIdempotencyKey(string $key): self
    {
        return new self("Transfer with idempotency key '{$key}' already processed");
    }
}
