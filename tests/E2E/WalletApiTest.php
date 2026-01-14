<?php

declare(strict_types=1);

use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->artisan('migrate:fresh');
});

test('wallet balance endpoint returns balance data', function (): void {
    $user = User::factory()->create();
    $walletId = Str::uuid()->toString();
    WalletAggregate::retrieve($walletId)
        ->createWallet($user->id)
        ->persist();

    $response = $this->actingAs($user)
        ->getJson('/api/wallet/balance');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['balance', 'currency']]);
});

test('wallet transactions endpoint returns data', function (): void {
    $user = User::factory()->create();
    $walletId = Str::uuid()->toString();
    WalletAggregate::retrieve($walletId)
        ->createWallet($user->id)
        ->persist();

    $response = $this->actingAs($user)
        ->getJson('/api/wallet/transactions');

    $response->assertStatus(200)
        ->assertJsonIsArray();
});

test('transfer endpoint requires authentication', function (): void {
    $response = $this->postJson('/api/transfers', [
        'recipient_email' => 'recipient@example.com',
        'amount' => '100.00',
    ]);

    $response->assertStatus(401);
});

test('login validates credentials', function (): void {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'naoexiste@example.com',
        'password' => 'senha12345',
    ]);

    $response->assertStatus(401);
});

test('logout requires authentication', function (): void {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401);
});

test('authenticated user can access protected routes', function (): void {
    $user = User::factory()->create();
    $walletId = Str::uuid()->toString();
    WalletAggregate::retrieve($walletId)
        ->createWallet($user->id)
        ->persist();

    $response = $this->actingAs($user)
        ->getJson('/api/wallet');

    $response->assertStatus(200);
});

test('wallet show endpoint returns wallet details', function (): void {
    $user = User::factory()->create();
    $walletId = Str::uuid()->toString();
    WalletAggregate::retrieve($walletId)
        ->createWallet($user->id)
        ->persist();

    $response = $this->actingAs($user)
        ->getJson('/api/wallet');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});
