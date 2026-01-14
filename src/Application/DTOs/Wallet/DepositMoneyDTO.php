<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

final readonly class DepositMoneyDTO
{
    public function __construct(
        public string $walletId,
        public string $amount,
        public string $idempotencyKey,
        public array $metadata = []
    ) {}

    public static function fromPrimitives(
        string $walletId,
        string $amount,
        string $idempotencyKey,
        array $metadata = []
    ): self {
        return new self(
            walletId: $walletId,
            amount: $amount,
            idempotencyKey: $idempotencyKey,
            metadata: $metadata
        );
    }
}
