<?php

declare(strict_types=1);

namespace App\Application\Contracts\Wallet;

use App\Application\DTOs\Wallet\TransferMoneyDTO;
use App\Application\DTOs\Wallet\TransferResultDTO;

interface TransferMoneyUseCaseInterface
{
    public function execute(TransferMoneyDTO $dto): TransferResultDTO;
}
