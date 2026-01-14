<?php

declare(strict_types=1);

namespace App\Domain\Wallet\DTOs;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Enums\Currency;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;
use DateTimeImmutable;

final readonly class WalletDTO
{
    public function __construct(
        public WalletId $id,
        public UserId $userId,
        public Money $balance,
        public Currency $currency,
        public DateTimeImmutable $createdAt
    ) {}

    public static function fromPrimitives(
        string $id,
        string $userId,
        int $balanceCents,
        string $currency,
        string $createdAt
    ): self {
        return new self(
            id: new WalletId($id),
            userId: new UserId($userId),
            balance: Money::fromCents($balanceCents, $currency),
            currency: Currency::from($currency),
            createdAt: new DateTimeImmutable($createdAt)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'user_id' => $this->userId->value,
            'balance' => $this->balance->toDecimal(),
            'balance_cents' => $this->balance->toCents(),
            'currency' => $this->currency->value,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
