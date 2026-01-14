<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\ValueObjects\WalletId;

interface AuthContextInterface
{
    public function getUserId(): UserId;

    public function getEmail(): Email;

    public function getWalletId(): WalletId;

    public function isAuthenticated(): bool;
}
