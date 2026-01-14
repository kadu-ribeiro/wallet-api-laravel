<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->wallet = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'balance_cents' => 10000,
        'currency' => 'BRL',
    ]);
});

test('wallet model balance works correctly', function (): void {
    expect($this->wallet->balance_cents)->toBe(10000);

    $this->wallet->balance_cents = 15000;
    $this->wallet->save();

    expect($this->wallet->fresh()->balance_cents)->toBe(15000);
});

test('wallet belongs to user', function (): void {
    expect($this->user->wallet)->not()->toBeNull()
        ->and($this->user->wallet->id)->toBe($this->wallet->id)
    ;
});

test('wallet has correct currency', function (): void {
    expect($this->wallet->currency)->toBe('BRL');
});

test('can create transaction record', function (): void {
    $transaction = $this->wallet->transactions()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'deposit',
        'amount_cents' => 5000,
        'description' => 'Test deposit',
        'balance_after_cents' => 15000,
        'idempotency_key' => Str::uuid()->toString(),
    ]);

    expect($transaction)->not()->toBeNull()
        ->and($transaction->amount_cents)->toBe(5000)
        ->and($transaction->type)->toBe('deposit')
    ;
});

test('wallet can have multiple transactions', function (): void {
    $this->wallet->transactions()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'deposit',
        'amount_cents' => 5000,
        'description' => 'Deposit 1',
        'balance_after_cents' => 15000,
        'idempotency_key' => Str::uuid()->toString(),
    ]);

    $this->wallet->transactions()->create([
        'id' => Str::uuid()->toString(),
        'type' => 'withdrawal',
        'amount_cents' => 3000,
        'description' => 'Withdrawal 1',
        'balance_after_cents' => 12000,
        'idempotency_key' => Str::uuid()->toString(),
    ]);

    expect($this->wallet->transactions()->count())->toBe(2);
});

test('user can access wallet via relationship', function (): void {
    $foundWallet = $this->user->wallet;

    expect($foundWallet->id)->toBe($this->wallet->id)
        ->and($foundWallet->balance_cents)->toBe(10000)
    ;
});
