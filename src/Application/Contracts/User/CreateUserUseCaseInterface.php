<?php

declare(strict_types=1);

namespace App\Application\Contracts\User;

use App\Application\DTOs\User\CreateUserDTO;
use App\Application\DTOs\User\AuthResultDTO;

interface CreateUserUseCaseInterface
{
    public function execute(CreateUserDTO $dto): AuthResultDTO;
}
