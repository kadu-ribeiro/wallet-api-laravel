<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\DTOs\UserDTO;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

interface UserRepositoryInterface
{
    public function findById(UserId $userId): ?UserDTO;

    public function findByEmail(Email $email): ?UserDTO;

    public function emailExists(Email $email): bool;
    
    public function createToken(UserId $userId, string $tokenName): string;
    
    public function revokeCurrentToken(UserId $userId): void;
    
    public function revokeAllTokens(UserId $userId): void;
    
    public function verifyPassword(Email $email, string $password): ?UserId;
}
