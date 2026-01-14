<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

interface AuthenticationServiceInterface
{
    public function createToken(UserId $userId, string $tokenName): string;

    public function revokeCurrentToken(UserId $userId): void;

    public function revokeAllTokens(UserId $userId): void;

    public function verifyPassword(Email $email, string $password): ?UserId;
}
