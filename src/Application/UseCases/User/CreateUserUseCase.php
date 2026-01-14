<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\Contracts\User\CreateUserUseCaseInterface;
use App\Application\DTOs\User\AuthResultDTO;
use App\Application\DTOs\User\CreateUserDTO;
use App\Application\DTOs\Wallet\CreateWalletDTO;
use App\Application\UseCases\Wallet\CreateWalletUseCase;
use App\Domain\User\Aggregates\UserAggregate;
use App\Domain\User\Exceptions\UserAlreadyExistsException;
use App\Domain\User\Exceptions\UserNotExistsException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\AuthenticationServiceInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Password;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

final readonly class CreateUserUseCase implements CreateUserUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CreateWalletUseCase $createWalletUseCase,
        private AuthenticationServiceInterface $authService
    ) {}

    public function execute(CreateUserDTO $dto): AuthResultDTO
    {
        $email = Email::from($dto->email);
        $name = UserName::from($dto->name);
        $password = Password::from($dto->password);

        if ($this->userRepository->emailExists($email)) {
            throw UserAlreadyExistsException::withEmail($dto->email);
        }

        $userId = new UserId(Str::orderedUuid()->toString());

        return DB::transaction(function () use ($userId, $name, $email, $password) {
            UserAggregate::retrieve($userId->value)
                ->createUser(
                    name: $name->value,
                    email: $email->value,
                    passwordHash: Hash::make($password->value)
                )
                ->persist()
            ;

            $walletIdString = $this->createWalletUseCase->execute(
                new CreateWalletDTO(userId: $userId->value)
            );

            $token = $this->authService->createToken($userId, 'api-token');

            return new AuthResultDTO(
                message: 'User registered successfully',
                userId: $userId->value,
                name: $name->value,
                email: $email->value,
                walletId: $walletIdString,
                token: $token
            );
        });
    }
}
