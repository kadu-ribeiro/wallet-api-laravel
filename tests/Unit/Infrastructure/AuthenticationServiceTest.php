<?php

use App\Domain\User\Services\AuthenticationServiceInterface;
use App\Domain\User\ValueObjects\{Email, UserId};
use App\Infrastructure\Persistence\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('createToken returns valid token string', function () {
    $service = app(AuthenticationServiceInterface::class);
    $user = User::factory()->create();
    
    $token = $service->createToken(new UserId($user->id), 'test-token');
    
    expect($token)->toBeString();
    expect(strlen($token))->toBeGreaterThan(40);
});

test('verifyPassword returns UserId on correct password', function () {
    $service = app(AuthenticationServiceInterface::class);
    $user = User::factory()->create(['password' => Hash::make('password123')]);
    
    $result = $service->verifyPassword(
        Email::from($user->email),
        'password123'
    );
    
    expect($result)->toBeInstanceOf(UserId::class);
    expect($result->value)->toBe($user->id);
});

test('verifyPassword returns null on wrong password', function () {
    $service = app(AuthenticationServiceInterface::class);
    $user = User::factory()->create(['password' => Hash::make('password123')]);
    
    $result = $service->verifyPassword(
        Email::from($user->email),
        'wrongpassword'
    );
    
    expect($result)->toBeNull();
});

test('revokeAllTokens deletes all user tokens', function () {
    $service = app(AuthenticationServiceInterface::class);
    $user = User::factory()->create();
    
    $user->createToken('token1');
    $user->createToken('token2');
    $user->createToken('token3');
    
    $service->revokeAllTokens(new UserId($user->id));
    
    // Refresh to get updated token count
    expect(User::find($user->id)->tokens()->count())->toBe(0);
});

test('multiple tokens can be created for same user', function () {
   $service = app(AuthenticationServiceInterface::class);
    $user = User::factory()->create();
    
    $token1 = $service->createToken(new UserId($user->id), 'device1');
    $token2 = $service->createToken(new UserId($user->id), 'device2');
    
    expect($token1)->not->toBe($token2);
    expect(User::find($user->id)->tokens()->count())->toBe(2);
});
