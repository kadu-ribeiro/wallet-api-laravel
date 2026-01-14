<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

final readonly class CreateWalletDTO
{
    public function __construct(
        public string $userId
    ) {}
}
