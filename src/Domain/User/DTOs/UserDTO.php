<?php

declare(strict_types=1);

namespace App\Domain\User\DTOs;

use App\Domain\User\ValueObjects\{Email, UserId};
use App\Domain\Wallet\ValueObjects\WalletId;

final readonly class UserDTO
{
    public function __construct(
        public UserId $id,
        public string $name,
        public Email $email,
        public ?WalletId $walletId,
        public \DateTimeImmutable $createdAt
    ) {}

    public static function fromPrimitives(
        string $id,
        string $name,
        string $email,
        ?string $walletId,
        string $createdAt
    ): self {
        return new self(
            id: new UserId($id),
            name: $name,
            email: Email::from($email),
            walletId: $walletId ? new WalletId($walletId) : null,
            createdAt: new \DateTimeImmutable($createdAt)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'name' => $this->name,
            'email' => $this->email->value,
            'wallet_id' => $this->walletId?->value,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
