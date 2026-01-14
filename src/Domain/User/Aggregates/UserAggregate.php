<?php

declare(strict_types=1);

namespace App\Domain\User\Aggregates;

use App\Domain\User\Events\UserCreated;
use App\Domain\User\Exceptions\UserNotExistsException;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

/**
 * User Aggregate Root.
 *
 * Manages user lifecycle using Event Sourcing.
 * Business rules: unique email, valid data.
 */
final class UserAggregate extends AggregateRoot
{
    private ?string $email = null;

    private bool $isCreated = false;

    public function createUser(
        string $name,
        string $email,
        string $passwordHash
    ): self {
        if ($this->isCreated) {
            throw new UserNotExistsException();
        }

        $this->recordThat(new UserCreated(
            userId: $this->uuid(),
            name: $name,
            email: $email,
            passwordHash: $passwordHash
        ));

        return $this;
    }

    // ========================================================================
    // Event Appliers (rebuild state from events)
    // ========================================================================

    protected function applyUserCreated(UserCreated $event): void
    {
        $this->email = $event->email;
        $this->isCreated = true;
    }
}
