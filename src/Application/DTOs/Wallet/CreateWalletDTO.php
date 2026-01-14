<?php

declare(strict_types=1);

namespace App\Application\DTOs\Wallet;

/**
 * DTO for wallet creation request.
 */
final readonly class CreateWalletDTO
{
    public function __construct(
        public string $userId  // UUID string, not integer
    ) {}
}
