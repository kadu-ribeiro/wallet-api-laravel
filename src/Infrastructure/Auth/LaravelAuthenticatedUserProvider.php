<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\User\Exceptions\UserHasNoWalletException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\AuthContextInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\ValueObjects\WalletId;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use RuntimeException;

final readonly class LaravelAuthenticatedUserProvider implements AuthContextInterface
{
    public function __construct(
        private AuthFactory $auth,
        private UserRepositoryInterface $userRepository
    ) {}

    public function getUserId(): UserId
    {
        $id = $this->auth->guard('sanctum')->id();

        if (! $id) {
            throw new RuntimeException('No authenticated user');
        }

        return new UserId((string) $id);
    }

    public function getEmail(): Email
    {
        $userId = $this->getUserId();
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new RuntimeException('Authenticated user not found');
        }

        $userData = $user->toArray();

        return Email::from($userData['email']);
    }

    public function getWalletId(): WalletId
    {
        $userId = $this->getUserId();
        $user = $this->userRepository->findById($userId);

        $userData = $user?->toArray();

        if (! $user || ! $userData['wallet_id']) {
            throw UserHasNoWalletException::create();
        }

        return new WalletId($userData['wallet_id']);
    }

    public function isAuthenticated(): bool
    {
        return $this->auth->guard('sanctum')->check();
    }
}
