<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('user model can be created with factory', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    expect($user)->not()->toBeNull()
        ->and($user->email)->toBe('test@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('user can have wallet relation', function (): void {
    $user = User::factory()->create();

    $wallet = Wallet::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'balance_cents' => 0,
        'currency' => 'BRL',
    ]);

    expect($user->wallet)->not()->toBeNull()
        ->and($user->wallet->id)->toBe($wallet->id);
});

test('email validation rejects invalid format', function (): void {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'João Silva',
        'email' => 'email-invalido',
        'password' => 'senha12345',
        'password_confirmation' => 'senha12345',
    ]);

    $response->assertStatus(422);
});

test('short password is rejected', function (): void {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(422);
});
