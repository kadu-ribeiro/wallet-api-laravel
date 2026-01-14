<?php

declare(strict_types=1);

namespace App\Application\Contracts\Wallet;

use App\Application\DTOs\Wallet\CreateWalletDTO;

interface CreateWalletUseCaseInterface
{
    public function execute(CreateWalletDTO $dto): string;
}
