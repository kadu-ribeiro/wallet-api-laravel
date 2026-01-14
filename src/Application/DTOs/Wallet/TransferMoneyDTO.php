<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

use App\Infrastructure\Http\Requests\TransferRequest;

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

    public static function fromRequest(TransferRequest $request): self
    {
        return new self(
            walletId: $request->senderWalletId() ?? '',
            recipientEmail: $request->recipientEmail(),
            amount: $request->amount(),
            idempotencyKey: $request->idempotencyKey(),
            userEmail: $request->senderEmail(),
            metadata: $request->metadata()
        );
    }
}
