<?php

declare(strict_types=1);

use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Illuminate\Support\Str;

test('can create wallet for user', function (): void {
    $user = User::factory()->create();

    $wallet = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'balance_cents' => 0,
        'currency' => 'BRL',
    ]);

    expect($wallet)->not()->toBeNull()
        ->and($wallet->user_id)->toBe($user->id)
        ->and($wallet->balance_cents)->toBe(0);
});

test('can deposit money and update balance', function (): void {
    $user = User::factory()->create();
    $wallet = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'balance_cents' => 0,
        'currency' => 'BRL',
    ]);

    $wallet->balance_cents = 10000;
    $wallet->save();

    expect($wallet->fresh()->balance_cents)->toBe(10000);
});

test('can withdraw money if sufficient balance', function (): void {
    $user = User::factory()->create();
    $wallet = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'balance_cents' => 10000,
        'currency' => 'BRL',
    ]);

    $wallet->balance_cents = 7000;
    $wallet->save();

    expect($wallet->fresh()->balance_cents)->toBe(7000);
});

test('cannot have negative balance', function (): void {
    $user = User::factory()->create();
    $walletId = Str::uuid()->toString();

    WalletAggregate::retrieve($walletId)
        ->createWallet($user->id)
        ->deposit(5000, ['idempotency_key' => Str::uuid()->toString()])
        ->persist();

    expect(fn () => WalletAggregate::retrieve($walletId)
        ->withdraw(6000, ['idempotency_key' => Str::uuid()->toString()])
        ->persist()
    )->toThrow(InsufficientBalanceException::class);
});

test('wallet user relationship works', function (): void {
    $user = User::factory()->create();
    $wallet = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'balance_cents' => 0,
        'currency' => 'BRL',
    ]);

    expect($user->wallet->id)->toBe($wallet->id);
});

test('multiple users can have wallets', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user1->id,
        'balance_cents' => 1000,
        'currency' => 'BRL',
    ]);

    $wallet2 = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user2->id,
        'balance_cents' => 2000,
        'currency' => 'BRL',
    ]);

    expect($user1->wallet->balance_cents)->toBe(1000)
        ->and($user2->wallet->balance_cents)->toBe(2000);
});
