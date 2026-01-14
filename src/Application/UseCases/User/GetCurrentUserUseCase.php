<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Domain\User\DTOs\UserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\AuthContextInterface;
use RuntimeException;

final readonly class GetCurrentUserUseCase
{
    public function __construct(
        private AuthContextInterface $authContext,
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(): UserDTO
    {
        $userId = $this->authContext->getUserId();
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new RuntimeException('Authenticated user not found');
        }

        return $user;
    }
}
