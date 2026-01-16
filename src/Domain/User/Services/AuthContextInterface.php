<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

interface AuthContextInterface
{
    public function getUserId(): UserId;

    public function getEmail(): Email;

    public function isAuthenticated(): bool;
}
