<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

abstract readonly class IntegerIdentifier
{
    public function __construct(
        public int $value
    ) {
        if ($value <= 0) {
            throw new InvalidArgumentException(
                sprintf('ID must be positive integer, got %d for %s', $value, static::class)
            );
        }
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value && static::class === $other::class;
    }
}
