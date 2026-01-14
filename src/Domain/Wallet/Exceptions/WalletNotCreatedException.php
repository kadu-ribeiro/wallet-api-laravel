<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use DomainException;

final class WalletNotCreatedException extends DomainException
{
    public static function create(): self
    {
        return new self('Wallet must be created before performing operations');
    }
}
