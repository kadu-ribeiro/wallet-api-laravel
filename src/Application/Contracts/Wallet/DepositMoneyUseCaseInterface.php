<?php

declare(strict_types=1);

namespace App\Application\Contracts\Wallet;

use App\Application\DTOs\Wallet\DepositMoneyDTO;
use App\Application\DTOs\Wallet\DepositResultDTO;

interface DepositMoneyUseCaseInterface
{
    public function execute(DepositMoneyDTO $dto): DepositResultDTO;
}
