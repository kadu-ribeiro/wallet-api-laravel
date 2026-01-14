<?php

declare(strict_types=1);

namespace App\Domain\Wallet\DTOs;

use App\Domain\User\ValueObjects\Email;
use App\Domain\Wallet\Enums\Currency;
use App\Domain\Wallet\Enums\TransactionType;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\TransactionId;
use App\Domain\Wallet\ValueObjects\WalletId;
use DateTimeImmutable;

final readonly class TransactionDTO
{
    public function __construct(
        public TransactionId $id,
        public WalletId $walletId,
        public TransactionType $type,
        public Money $amount,
        public Money $balanceAfter,
        public Currency $currency,
        public ?Email $relatedUserEmail,
        public ?TransactionId $relatedTransactionId,
        public array $metadata,
        public DateTimeImmutable $createdAt
    ) {}

    public static function fromPrimitives(
        int $id,
        string $walletId,
        string $type,
        int $amountCents,
        int $balanceAfterCents,
        string $currency,
        ?string $relatedUserEmail,
        ?int $relatedTransactionId,
        array $metadata,
        string $createdAt
    ): self {
        return new self(
            id: new TransactionId($id),
            walletId: new WalletId($walletId),
            type: TransactionType::from($type),
            amount: Money::fromCents($amountCents, $currency),
            balanceAfter: Money::fromCents($balanceAfterCents, $currency),
            currency: Currency::from($currency),
            relatedUserEmail: $relatedUserEmail ? Email::from($relatedUserEmail) : null,
            relatedTransactionId: $relatedTransactionId ? new TransactionId($relatedTransactionId) : null,
            metadata: $metadata,
            createdAt: new DateTimeImmutable($createdAt)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'wallet_id' => $this->walletId->value,
            'type' => $this->type->value,
            'amount_cents' => $this->amount->toCents(),
            'amount' => $this->amount->toDecimal(),
            'balance_after_cents' => $this->balanceAfter->toCents(),
            'balance_after' => $this->balanceAfter->toDecimal(),
            'currency' => $this->currency->value,
            'related_user_email' => $this->relatedUserEmail?->value,
            'related_transaction_id' => $this->relatedTransactionId?->value,
            'metadata' => empty($this->metadata) ? (object)[] : $this->metadata,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
