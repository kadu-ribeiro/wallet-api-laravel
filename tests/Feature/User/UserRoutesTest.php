<?php

declare(strict_types=1);

use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Infrastructure\Persistence\Eloquent\User;

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
        ->getJson('/api/user')
    ;

    $response->assertOk()
        ->assertJsonPath('data.id', $userId)
        ->assertJsonPath('data.name', 'Test User')
        ->assertJsonPath('data.email', 'test@example.com')
        ->assertJsonPath('data.wallet_id', $walletId)
    ;
});

test('unauthenticated user cannot get user data', function (): void {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

test('can get wallet by user id', function (): void {
    $registerResponse = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $token = $registerResponse->json('token');
    $userId = $registerResponse->json('user.id');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/users/{$userId}/wallet")
    ;

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'balance',
                'currency',
            ],
        ])
    ;
});

test('returns 404 when wallet not found for user', function (): void {
    $registerResponse = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $token = $registerResponse->json('token');

    $userWithoutWallet = User::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/users/{$userWithoutWallet->id}/wallet")
    ;

    $response->assertNotFound()
        ->assertJsonPath('error', fn ($value) => str_contains($value, 'not found'))
    ;
});

test('unauthenticated user cannot get wallet by user id', function (): void {
    $response = $this->getJson('/api/users/some-uuid/wallet');

    $response->assertUnauthorized();
});

test('authenticated user can get their own data via usecase', function (): void {
    $user = User::factory()->create();

    WalletAggregate::retrieve($user->id)
        ->createWallet($user->id, 'BRL')
        ->persist()
    ;

    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertStatus(200)
        ->assertJsonPath('data.email', $user->email)
    ;
});

test('can retrieve wallet by user id', function (): void {
    $user = User::factory()->create();
    $walletId = Str::uuid()->toString();

    WalletAggregate::retrieve($walletId)
        ->createWallet($user->id)
        ->persist()
    ;

    $this->actingAs($user)
        ->getJson("/api/users/{$user->id}/wallet")
        ->assertStatus(200)
        ->assertJsonPath('data.user_id', $user->id)
    ;
});
