<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\User\DTOs\UserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\User;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function findById(UserId $userId): ?UserDTO
    {
        $user = User::find($userId->value);

        if (! $user) {
            return null;
        }

        return UserDTO::fromPrimitives(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            walletId: $user->wallet?->id,
            createdAt: $user->created_at->toIso8601String()
        );
    }

    public function findByEmail(Email $email): ?UserDTO
    {
        $user = User::where('email', $email->value)->first();

        if (! $user) {
            return null;
        }

        return UserDTO::fromPrimitives(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            walletId: $user->wallet?->id,
            createdAt: $user->created_at->toIso8601String()
        );
    }

    public function emailExists(Email $email): bool
    {
        return User::where('email', $email->value)->exists();
    }
    
    public function createToken(UserId $userId, string $tokenName): string
    {
        $user = User::find($userId->value);
        
        if (! $user) {
            throw new \RuntimeException("User not found: {$userId->value}");
        }
        
        return $user->createToken($tokenName)->plainTextToken;
    }
    
    public function revokeCurrentToken(UserId $userId): void
    {
        $user = User::find($userId->value);
        
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
    }
    
    public function revokeAllTokens(UserId $userId): void
    {
        $user = User::find($userId->value);
        
        if ($user) {
            $user->tokens()->delete();
        }
    }
    
    public function verifyPassword(Email $email, string $password): ?UserId
    {
        $user = User::where('email', $email->value)->first();
        
        if (! $user || ! \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return null;
        }
        
        return new UserId($user->id);
    }
}
