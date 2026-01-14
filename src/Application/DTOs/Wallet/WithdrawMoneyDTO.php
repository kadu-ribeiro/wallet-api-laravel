<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

use App\Infrastructure\Http\Requests\WithdrawRequest;

final readonly class WithdrawMoneyDTO
{
    public function __construct(
        public string $walletId,
        public string $amount,
        public string $idempotencyKey,
        public array $metadata = []
    ) {}

    public static function fromRequest(WithdrawRequest $request): self
    {
        return new self(
            walletId: $request->walletId(),
            amount: $request->amount(),
            idempotencyKey: $request->idempotencyKey(),
            metadata: $request->metadata()
        );
    }
}
