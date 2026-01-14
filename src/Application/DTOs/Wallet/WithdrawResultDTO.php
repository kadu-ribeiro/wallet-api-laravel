<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

final readonly class WithdrawResultDTO
{
    public function __construct(
        public string $message,
        public string $walletId,
        public int $balanceCents,
        public string $balance,
        public string $currency
    ) {}

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'wallet' => [
                'id' => $this->walletId,
                'balance_cents' => $this->balanceCents,
                'balance' => $this->balance,
                'currency' => $this->currency,
            ],
        ];
    }
}
