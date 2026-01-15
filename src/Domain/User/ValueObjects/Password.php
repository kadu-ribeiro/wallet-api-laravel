<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidPasswordException;

final readonly class Password
{
    private const MIN_LENGTH = 8;

    private function __construct(
        public string $value
    ) {}

    public static function from(string $password): self
    {
        if ($password === '') {
            throw InvalidPasswordException::empty();
        }

        if (mb_strlen($password) < self::MIN_LENGTH) {
            throw InvalidPasswordException::tooShort(self::MIN_LENGTH);
        }

        return new self($password);
    }
}
