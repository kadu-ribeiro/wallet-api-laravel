<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Support\Facades\Hash;

test('user cannot login without credentials', function (): void {
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(422);
});

test('user cannot login with non-existent email', function (): void {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'naoexiste@example.com',
        'password' => 'senha12345',
    ]);

    $response->assertStatus(401);
});

test('user cannot login with invalid password', function (): void {
    $user = User::factory()->create([
        'email' => 'joao@example.com',
        'password' => Hash::make('senha12345'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'joao@example.com',
        'password' => 'senha-errada',
    ]);

    $response->assertStatus(401)
        ->assertJson(['error' => 'Invalid email or password'])
    ;
});
