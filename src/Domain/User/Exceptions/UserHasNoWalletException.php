<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use Exception;

final class UserHasNoWalletException extends Exception
{
    public static function create(): self
    {
        return new self('User has no wallet associated', 404);
    }
}
