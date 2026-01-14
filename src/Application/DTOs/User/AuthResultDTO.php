<?php

declare(strict_types=1);

namespace App\Application\DTOs\User;

final readonly class AuthResultDTO
{
    public function __construct(
        public string $message,
        public string $userId,
        public string $name,
        public string $email,
        public string $walletId,
        public string $token
    ) {}

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'user' => [
                'id' => $this->userId,
                'name' => $this->name,
                'email' => $this->email,
            ],
            'wallet_id' => $this->walletId,
            'token' => $this->token,
        ];
    }
}
