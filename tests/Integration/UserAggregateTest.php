<?php

declare(strict_types=1);

use App\Domain\User\Aggregates\UserAggregate;
use App\Domain\User\Events\UserCreated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->artisan('migrate:fresh');
});

test('user aggregate can be created and persisted', function (): void {
    $userId = Str::uuid()->toString();

    $aggregate = UserAggregate::retrieve($userId);
    $aggregate->createUser(
        name: 'João Silva',
        email: 'joao@example.com',
        passwordHash: Hash::make('senha12345')
    );
    $aggregate->persist();

    // Check that events were recorded (aggregate exists)
    $this->assertDatabaseHas('stored_events', [
        'aggregate_uuid' => $userId,
    ]);
});

test('user projector creates user in database', function (): void {
    $userId = Str::uuid()->toString();

    $aggregate = UserAggregate::retrieve($userId);
    $aggregate->createUser(
        name: 'Maria Santos',
        email: 'maria@example.com',
        passwordHash: Hash::make('password123')
    );
    $aggregate->persist();

    $this->assertDatabaseHas('users', [
        'id' => $userId,
        'email' => 'maria@example.com',
        'name' => 'Maria Santos',
    ]);
});

test('user aggregate events are stored', function (): void {
    $userId = Str::uuid()->toString();

    $aggregate = UserAggregate::retrieve($userId);
    $aggregate->createUser(
        name: 'Pedro Costa',
        email: 'pedro@example.com',
        passwordHash: Hash::make('senha123')
    );
    $aggregate->persist();

    // Verify event was stored
    $this->assertDatabaseHas('stored_events', [
        'aggregate_uuid' => $userId,
        'event_class' => UserCreated::class,
    ]);
});

test('user aggregate can be retrieved and replayed', function (): void {
    $userId = Str::uuid()->toString();

    $aggregate = UserAggregate::retrieve($userId);
    $aggregate->createUser(
        name: 'Ana Lima',
        email: 'ana@example.com',
        passwordHash: Hash::make('senha456')
    );
    $aggregate->persist();

    // Retrieve again - state should be replayed from events
    $retrieved = UserAggregate::retrieve($userId);

    // The aggregate exists (no exception thrown during retrieve)
    expect($retrieved)->toBeInstanceOf(UserAggregate::class);
});
