<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

final class InvalidPasswordException extends DomainException
{
    public static function empty(): self
    {
        return new self('Password cannot be empty');
    }

    public static function tooShort(int $minLength): self
    {
        return new self("Password must be at least {$minLength} characters");
    }
}
