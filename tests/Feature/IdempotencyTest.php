<?php

declare(strict_types=1);

use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $walletId = Str::uuid()->toString();
    WalletAggregate::retrieve($walletId)
        ->createWallet($this->user->id)
        ->persist()
    ;
    $this->walletId = $walletId;
    $this->token = $this->user->createToken('test')->plainTextToken;
});

test('deposit without idempotency key returns 400', function (): void {
    $response = $this->withToken($this->token)
        ->postJson("/api/wallets/{$this->walletId}/deposit", [
            'amount' => '100.00',
        ])
    ;

    $response->assertStatus(400)
        ->assertJson(['error' => 'Idempotency-Key header is required for this operation'])
    ;
});

test('deposit with invalid idempotency key returns 422', function (): void {
    $response = $this->withToken($this->token)
        ->postJson("/api/wallets/{$this->walletId}/deposit", [
            'amount' => '100.00',
        ], ['Idempotency-Key' => 'not-a-uuid'])
    ;

    $response->assertStatus(422)
        ->assertJson(['error' => 'Idempotency-Key must be a valid UUID'])
    ;
});

test('deposit with valid idempotency key succeeds', function (): void {
    $key = Str::uuid()->toString();

    $response = $this->withToken($this->token)
        ->postJson("/api/wallets/{$this->walletId}/deposit", [
            'amount' => '100.00',
        ], ['Idempotency-Key' => $key])
    ;

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'wallet' => ['id', 'balance_cents', 'balance', 'currency'],
        ])
    ;
});

test('repeated transfer with same idempotency key is rejected with 409', function (): void {
    $sender = User::factory()->create();
    $senderWalletId = Str::uuid()->toString();
    WalletAggregate::retrieve($senderWalletId)
        ->createWallet($sender->id)
        ->persist()
    ;
    $senderToken = $sender->createToken('test')->plainTextToken;

    $recipient = User::factory()->create();
    $recipientWalletId = Str::uuid()->toString();
    WalletAggregate::retrieve($recipientWalletId)
        ->createWallet($recipient->id)
        ->persist()
    ;

    $this->withToken($senderToken)
        ->postJson("/api/wallets/{$senderWalletId}/deposit", [
            'amount' => '100.00',
        ], ['Idempotency-Key' => Str::uuid()->toString()])
    ;

    $key = Str::uuid()->toString();

    $response1 = $this->withToken($senderToken)
        ->postJson('/api/transfers', [
            'recipient_email' => $recipient->email,
            'amount' => '50.00',
        ], ['Idempotency-Key' => $key])
    ;

    $response1->assertStatus(200);

    $response2 = $this->withToken($senderToken)
        ->postJson('/api/transfers', [
            'recipient_email' => $recipient->email,
            'amount' => '50.00',
        ], ['Idempotency-Key' => $key])
    ;

    $response2->assertStatus(409);

    $senderWallet = Wallet::find($senderWalletId);
    expect($senderWallet->balance_cents)->toBe(5000);
});
