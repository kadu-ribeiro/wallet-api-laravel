<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use Exception;

final class SelfTransferNotAllowedException extends Exception
{
    public static function create(): self
    {
        return new self('You cannot transfer money to yourself', 422);
    }
}
