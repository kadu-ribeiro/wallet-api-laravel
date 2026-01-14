<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Domain\User\DTOs\UserDTO;
use App\Domain\User\Exceptions\UserNotExistsException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

final readonly class GetUserByIdUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $userId): UserDTO
    {
        $user = $this->userRepository->findById(new UserId($userId));

        if (! $user) {
            throw UserNotExistsException::withId($userId);
        }

        return $user;
    }
}
