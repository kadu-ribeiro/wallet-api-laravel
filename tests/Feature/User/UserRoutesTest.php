<?php

declare(strict_types=1);

use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Support\Str;

test('authenticated user can get their data', function (): void {
    $registerResponse = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $token = $registerResponse->json('token');
    $walletId = $registerResponse->json('wallet_id');
    $userId = $registerResponse->json('user.id');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user');

    $response->assertOk()
        ->assertJsonPath('data.id', $userId)
        ->assertJsonPath('data.name', 'Test User')
        ->assertJsonPath('data.email', 'test@example.com')
        ->assertJsonPath('data.wallet_id', $walletId);
});

test('unauthenticated user cannot get user data', function (): void {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

test('can get current user wallet', function (): void {
    $registerResponse = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $token = $registerResponse->json('token');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/wallet');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'balance',
                'currency',
            ],
        ]);
});

test('unauthenticated user cannot get wallet', function (): void {
    $response = $this->getJson('/api/wallet');

    $response->assertUnauthorized();
});

test('authenticated user can get their own data via usecase', function (): void {
    $user = User::factory()->create();

    WalletAggregate::retrieve($user->id)
        ->createWallet($user->id, 'BRL')
        ->persist();

    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertStatus(200)
        ->assertJsonPath('data.email', $user->email);
});

test('can retrieve wallet via actingAs', function (): void {
    $user = User::factory()->create();
    $walletId = Str::uuid()->toString();

    WalletAggregate::retrieve($walletId)
        ->createWallet($user->id)
        ->persist();

    $this->actingAs($user)
        ->getJson('/api/wallet')
        ->assertStatus(200)
        ->assertJsonPath('data.user_id', $user->id);
});
