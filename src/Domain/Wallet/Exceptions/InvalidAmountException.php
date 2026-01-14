<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use Exception;

final class InvalidAmountException extends Exception
{
    public static function mustBePositive(): self
    {
        return new self('Amount must be positive');
    }
}
