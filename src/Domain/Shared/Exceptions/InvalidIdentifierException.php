<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use Exception;

final class InvalidIdentifierException extends Exception
{
    public static function invalidUuid(string $value, string $class): self
    {
        return new self(sprintf('"%s" is not a valid UUID for %s', $value, $class));
    }

    public static function mustBePositive(int $value, string $class): self
    {
        return new self(sprintf('ID must be positive integer, got %d for %s', $value, $class));
    }
}
