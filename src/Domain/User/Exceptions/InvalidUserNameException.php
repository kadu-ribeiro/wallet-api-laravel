<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

final class InvalidUserNameException extends DomainException
{
    public static function empty(): self
    {
        return new self('User name cannot be empty');
    }

    public static function tooShort(int $minLength): self
    {
        return new self("User name must be at least {$minLength} characters");
    }

    public static function tooLong(int $maxLength): self
    {
        return new self("User name cannot exceed {$maxLength} characters");
    }
}
