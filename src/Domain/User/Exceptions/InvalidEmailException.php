<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

final class InvalidEmailException extends DomainException
{
    public static function invalidFormat(string $email): self
    {
        return new self("Invalid email format: {$email}");
    }

    public static function empty(): self
    {
        return new self('Email cannot be empty');
    }
}
