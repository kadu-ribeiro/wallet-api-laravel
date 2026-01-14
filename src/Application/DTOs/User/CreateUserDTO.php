<?php

declare(strict_types=1);

namespace App\Application\DTOs\User;

use App\Infrastructure\Http\Requests\RegisterUserRequest;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}

    public static function fromRequest(RegisterUserRequest $request): self
    {
        return new self(
            name: $request->userName(),
            email: $request->userEmail(),
            password: $request->userPassword()
        );
    }
}
