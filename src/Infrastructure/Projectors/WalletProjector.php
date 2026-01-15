<?php

declare(strict_types=1);

namespace App\Infrastructure\Projectors;

use App\Domain\Wallet\Events\MoneyDeposited;
use App\Domain\Wallet\Events\MoneyTransferredIn;
use App\Domain\Wallet\Events\MoneyTransferredOut;
use App\Domain\Wallet\Events\MoneyWithdrawn;
use App\Domain\Wallet\Events\WalletCreated;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

final class WalletProjector extends Projector
{
    public function onWalletCreated(WalletCreated $event): void
    {
        Wallet::create([
            'id' => $event->walletId,
            'user_id' => $event->userId,
            'balance_cents' => 0,
            'currency' => $event->currency,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function onMoneyDeposited(MoneyDeposited $event): void
    {
        Wallet::where('id', $event->walletId)
            ->update(['balance_cents' => $event->balanceAfterCents]);
    }

    public function onMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        Wallet::where('id', $event->walletId)
            ->update(['balance_cents' => $event->balanceAfterCents]);
    }

    public function onMoneyTransferredOut(MoneyTransferredOut $event): void
    {
        Wallet::where('id', $event->walletId)
            ->update(['balance_cents' => $event->balanceAfterCents]);
    }

    public function onMoneyTransferredIn(MoneyTransferredIn $event): void
    {
        Wallet::where('id', $event->walletId)
            ->update(['balance_cents' => $event->balanceAfterCents]);
    }
}
