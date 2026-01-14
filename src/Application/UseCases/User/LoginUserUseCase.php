<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\Contracts\User\LoginUserUseCaseInterface;
use App\Application\DTOs\User\AuthResultDTO;
use App\Domain\User\Exceptions\InvalidCredentialsException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\AuthenticationServiceInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final readonly class LoginUserUseCase implements LoginUserUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepositoryInterface $walletRepository,
        private AuthenticationServiceInterface $authService
    ) {}

    public function execute(string $email, string $password): AuthResultDTO
    {
        $emailVO = Email::from($email);
        
        $userId = $this->authService->verifyPassword($emailVO, $password);
        
        if (! $userId) {
            throw InvalidCredentialsException::create();
        }

        $user = $this->userRepository->findById($userId);
        $wallet = $this->walletRepository->findByUserId($userId);

        $token = $this->authService->createToken($userId, 'api-token');

        $userData = $user->toArray();
        $walletData = $wallet?->toArray();

        return new AuthResultDTO(
            message: 'Login successful',
            userId: $userData['id'],
            name: $userData['name'],
            email: $userData['email'],
            walletId: $walletData['id'] ?? null,
            token: $token
        );
    }
}
