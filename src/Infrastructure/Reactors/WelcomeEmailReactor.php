<?php

declare(strict_types=1);

namespace App\Infrastructure\Reactors;

use App\Domain\User\Events\UserCreated;
use App\Infrastructure\Mail\WelcomeEmail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

final class WelcomeEmailReactor extends Reactor implements ShouldQueue
{
    public function onUserCreated(UserCreated $event): void
    {
        try {
            Mail::to($event->email)->send(
                new WelcomeEmail(
                    userName: $event->name,
                    walletId: $event->aggregateRootUuid()
                )
            );
        } catch (Exception $e) {
            Log::warning('Failed to send welcome email', [
                'email' => $event->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
