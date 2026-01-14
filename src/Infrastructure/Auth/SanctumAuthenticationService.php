<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\AuthenticationServiceInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

final readonly class SanctumAuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function createToken(UserId $userId, string $tokenName): string
    {
        return $this->userRepository->createToken($userId, $tokenName);
    }

    public function revokeCurrentToken(UserId $userId): void
    {
        $this->userRepository->revokeCurrentToken($userId);
    }

    public function revokeAllTokens(UserId $userId): void
    {
        $this->userRepository->revokeAllTokens($userId);
    }

    public function verifyPassword(Email $email, string $password): ?UserId
    {
        return $this->userRepository->verifyPassword($email, $password);
    }
}
