<?php

declare(strict_types=1);

namespace App\Infrastructure\Projectors;

use App\Domain\User\Events\UserCreated;
use App\Infrastructure\Persistence\Eloquent\User;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

final class UserProjector extends Projector
{
    public function onUserCreated(UserCreated $event): void
    {
        User::create([
            'id' => $event->userId,
            'name' => $event->name,
            'email' => $event->email,
            'password' => $event->passwordHash,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
