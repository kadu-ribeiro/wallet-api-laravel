<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class MoneyTransferredIn extends ShouldBeStored
{
    public function __construct(
        public readonly string $walletId,
        public readonly int $amountCents,
        public readonly int $balanceAfterCents,
        public readonly string $senderEmail,
        public readonly string $transferId,
        public readonly array $metadata = []
    ) {}
}
