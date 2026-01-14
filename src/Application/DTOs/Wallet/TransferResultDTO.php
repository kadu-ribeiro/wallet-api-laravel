<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

final readonly class TransferResultDTO
{
    public function __construct(
        public string $message,
        public string $transferId,
        public string $walletId,
        public int $balanceCents,
        public string $balance,
        public string $recipientWalletId,
        public int $recipientBalanceCents,
        public string $recipientBalance,
        public int $amountCents,
        public string $amount,
        public string $currency
    ) {}

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'transfer_id' => $this->transferId,
            'sender' => [
                'wallet_id' => $this->walletId,
                'balance_cents' => $this->balanceCents,
                'balance' => $this->balance,
            ],
            'recipient' => [
                'wallet_id' => $this->recipientWalletId,
                'balance_cents' => $this->recipientBalanceCents,
                'balance' => $this->recipientBalance,
            ],
            'amount_cents' => $this->amountCents,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
