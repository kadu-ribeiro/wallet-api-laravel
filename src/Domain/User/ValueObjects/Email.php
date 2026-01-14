<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidEmailException;

final readonly class Email
{
    private function __construct(
        public string $value
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }

    public static function from(string $email): self
    {
        $email = strtolower(trim($email));

        if ('' === $email) {
            throw InvalidEmailException::empty();
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::invalidFormat($email);
        }

        return new self($email);
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
