<?php

declare(strict_types=1);

namespace App\Application\DTOs\User;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}

    public static function fromPrimitives(
        string $name,
        string $email,
        string $password
    ): self {
        return new self(
            name: $name,
            email: $email,
            password: $password
        );
    }
}
