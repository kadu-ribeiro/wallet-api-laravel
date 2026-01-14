<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

final readonly class TransferMoneyDTO
{
    public function __construct(
        public string $walletId,
        public string $recipientEmail,
        public string $amount,
        public string $idempotencyKey,
        public ?string $userEmail = null,
        public array $metadata = []
    ) {}

    public static function fromPrimitives(
        string $walletId,
        string $recipientEmail,
        string $amount,
        string $idempotencyKey,
        ?string $userEmail = null,
        array $metadata = []
    ): self {
        return new self(
            walletId: $walletId,
            recipientEmail: $recipientEmail,
            amount: $amount,
            idempotencyKey: $idempotencyKey,
            userEmail: $userEmail,
            metadata: $metadata
        );
    }
}
