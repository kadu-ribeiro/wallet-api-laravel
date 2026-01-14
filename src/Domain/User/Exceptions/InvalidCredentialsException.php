<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

final class InvalidCredentialsException extends DomainException
{
    public static function create(): self
    {
        return new self('Invalid email or password');
    }
}
