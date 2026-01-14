<?php

declare(strict_types=1);

namespace App\Application\Contracts\Wallet;

use App\Application\DTOs\Wallet\WithdrawMoneyDTO;
use App\Application\DTOs\Wallet\WithdrawResultDTO;

interface WithdrawMoneyUseCaseInterface
{
    public function execute(WithdrawMoneyDTO $dto): WithdrawResultDTO;
}
