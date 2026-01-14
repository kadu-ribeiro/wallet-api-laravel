<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class WalletCreated extends ShouldBeStored
{
    public function __construct(
        public readonly string $walletId,
        public readonly string $userId,
        public readonly string $currency = 'BRL'
    ) {}
}
