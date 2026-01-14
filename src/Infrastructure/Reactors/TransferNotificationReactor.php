<?php

declare(strict_types=1);

namespace App\Infrastructure\Reactors;

use App\Domain\Wallet\Events\MoneyTransferredIn;
use App\Infrastructure\Mail\TransferReceivedEmail;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

final class TransferNotificationReactor extends Reactor implements ShouldQueue
{
    public function onMoneyTransferredIn(MoneyTransferredIn $event): void
    {
        $wallet = Wallet::find($event->aggregateRootUuid());

        if (! $wallet) {
            return;
        }

        $user = $wallet->user;

        try {
            Mail::to($user->email)->send(
                new TransferReceivedEmail(
                    userName: $user->name,
                    amount: number_format($event->amountCents / 100, 2, ',', '.'),
                    senderEmail: $event->senderEmail,
                    newBalance: number_format($event->balanceAfterCents / 100, 2, ',', '.')
                )
            );
        } catch (Exception $e) {
            Log::warning('Failed to send transfer notification email', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
