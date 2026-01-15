<?php

declare(strict_types=1);

use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Domain\Wallet\Exceptions\DailyLimitExceededException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->artisan('migrate:fresh');

    // Create a user first (needed for FK constraint in projector)
    $this->user = User::factory()->create();
});

test('wallet aggregate can be created', function (): void {
    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->persist();

    expect($aggregate->getBalance())->toBe(0);
});

test('wallet aggregate can deposit money', function (): void {
    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(10000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->persist();

    expect($aggregate->getBalance())->toBe(10000);
});

test('wallet aggregate can withdraw money', function (): void {
    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(10000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->withdraw(3000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->persist();

    expect($aggregate->getBalance())->toBe(7000);
});

test('wallet aggregate throws on insufficient balance', function (): void {
    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(5000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->withdraw(10000, ['idempotency_key' => Str::uuid()->toString()]);
})->throws(InsufficientBalanceException::class);

test('wallet aggregate events are stored and reconstructed', function (): void {
    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(10000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->persist();

    // Retrieve again and check state is reconstructed from events
    $retrieved = WalletAggregate::retrieve($walletId);

    expect($retrieved->getBalance())->toBe(10000);
});

test('wallet aggregate transfer out reduces balance', function (): void {
    $walletId = Str::uuid()->toString();
    $transferId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(10000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->transferOut(5000, 'recipient@example.com', $transferId);
    $aggregate->persist();

    expect($aggregate->getBalance())->toBe(5000);
});

test('wallet aggregate transfer in increases balance', function (): void {
    $walletId = Str::uuid()->toString();
    $transferId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->transferIn(5000, 'sender@example.com', $transferId);
    $aggregate->persist();

    expect($aggregate->getBalance())->toBe(5000);
});

test('wallet projector creates wallet in database', function (): void {
    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->persist();

    $this->assertDatabaseHas('wallets', [
        'id' => $walletId,
        'user_id' => $this->user->id,
    ]);
});

test('wallet projector updates balance after deposit', function (): void {
    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(15000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->persist();

    $this->assertDatabaseHas('wallets', [
        'id' => $walletId,
        'balance_cents' => 15000,
    ]);
});

test('wallet aggregate throws on daily withdrawal limit exceeded', function (): void {
    config(['wallet.daily_limits.withdrawal' => 100000]);

    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(500000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->withdraw(60000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->withdraw(50000, ['idempotency_key' => Str::uuid()->toString()]);
})->throws(DailyLimitExceededException::class);

test('wallet aggregate throws on daily transfer limit exceeded', function (): void {
    config(['wallet.daily_limits.transfer' => 100000]);

    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(500000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->transferOut(60000, 'recipient@example.com', Str::uuid()->toString());
    $aggregate->transferOut(50000, 'recipient@example.com', Str::uuid()->toString());
})->throws(DailyLimitExceededException::class);

test('wallet aggregate allows withdrawal within daily limit', function (): void {
    config(['wallet.daily_limits.withdrawal' => 100000]);

    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(500000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->withdraw(50000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->withdraw(50000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->persist();

    expect($aggregate->getBalance())->toBe(400000);
});

test('wallet aggregate allows transfer within daily limit', function (): void {
    config(['wallet.daily_limits.transfer' => 100000]);

    $walletId = Str::uuid()->toString();

    $aggregate = WalletAggregate::retrieve($walletId);
    $aggregate->createWallet($this->user->id, 'BRL');
    $aggregate->deposit(500000, ['idempotency_key' => Str::uuid()->toString()]);
    $aggregate->transferOut(50000, 'recipient@example.com', Str::uuid()->toString());
    $aggregate->transferOut(50000, 'recipient@example.com', Str::uuid()->toString());
    $aggregate->persist();

    expect($aggregate->getBalance())->toBe(400000);
});
