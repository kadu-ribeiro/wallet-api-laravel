<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidIdentifierException;

abstract readonly class UuidIdentifier
{
    public function __construct(
        public string $value
    ) {
        if (! self::isValid($value)) {
            throw InvalidIdentifierException::invalidUuid($value, static::class);
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function isValid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value && static::class === $other::class;
    }
}
