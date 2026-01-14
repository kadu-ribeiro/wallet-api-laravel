<?php

declare(strict_types=1);

namespace App\Application\Contracts\User;

use App\Application\DTOs\User\AuthResultDTO;

interface LoginUserUseCaseInterface
{
    public function execute(string $email, string $password): AuthResultDTO;
}
