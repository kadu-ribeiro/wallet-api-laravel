<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

abstract readonly class UuidIdentifier
{
    public function __construct(
        public string $value
    ) {
        if (! self::isValid($value)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid UUID for %s', $value, static::class)
            );
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function isValid(string $value): bool
    {
        return 1 === preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value && static::class === $other::class;
    }
}
