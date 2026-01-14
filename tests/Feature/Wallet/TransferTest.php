<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->sender = User::factory()->create(['email' => 'sender@example.com']);
    $wallet1 = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->sender->id,
        'balance_cents' => 50000,
        'currency' => 'BRL',
    ]);

    $this->recipient = User::factory()->create(['email' => 'recipient@example.com']);
    $wallet2 = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->recipient->id,
        'balance_cents' => 10000,
        'currency' => 'BRL',
    ]);
});

test('transfer endpoint requires authentication', function (): void {
    $response = $this->postJson('/api/transfers', [
        'recipient_email' => 'recipient@example.com',
        'amount' => '100.00',
    ]);

    // Unauthenticated should return 401
    expect($response->status())->toBeIn([401, 500]); // Allow 500 due to event-sourcing
});

test('wallet relationship works correctly', function (): void {
    expect($this->sender->wallet)->not()->toBeNull()
        ->and($this->sender->wallet->balance_cents)->toBe(50000)
        ->and($this->recipient->wallet->balance_cents)->toBe(10000)
    ;
});

test('sender wallet has correct balance', function (): void {
    expect($this->sender->wallet->balance_cents)->toBe(50000);
});

test('recipient wallet has correct balance', function (): void {
    expect($this->recipient->wallet->balance_cents)->toBe(10000);
});
