<?php

declare(strict_types=1);

namespace App\Domain\User\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class UserCreated extends ShouldBeStored
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $email,
        public string $passwordHash
    ) {}
}
