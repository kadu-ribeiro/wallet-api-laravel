<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidUserNameException;

final readonly class UserName
{
    private const MIN_LENGTH = 2;

    private const MAX_LENGTH = 255;

    private function __construct(
        public string $value
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }

    public static function from(string $name): self
    {
        $name = trim($name);

        if ($name === '') {
            throw InvalidUserNameException::empty();
        }

        if (mb_strlen($name) < self::MIN_LENGTH) {
            throw InvalidUserNameException::tooShort(self::MIN_LENGTH);
        }

        if (mb_strlen($name) > self::MAX_LENGTH) {
            throw InvalidUserNameException::tooLong(self::MAX_LENGTH);
        }

        return new self($name);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
