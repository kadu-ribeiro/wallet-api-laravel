<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Exceptions;

use App\Domain\Shared\Exceptions\InternalServerErrorException;
use App\Domain\User\Exceptions\InvalidCredentialsException;
use App\Domain\User\Exceptions\InvalidEmailException;
use App\Domain\User\Exceptions\InvalidPasswordException;
use App\Domain\User\Exceptions\InvalidUserNameException;
use App\Domain\User\Exceptions\UserAlreadyExistsException;
use App\Domain\User\Exceptions\UserNotExistsException;
use App\Domain\Wallet\Exceptions\DailyLimitExceededException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidAmountException;
use App\Domain\Wallet\Exceptions\RecipientNotFoundException;
use App\Domain\Wallet\Exceptions\SelfTransferNotAllowedException;
use App\Domain\Wallet\Exceptions\TransferAlreadyProcessedException;
use App\Domain\Wallet\Exceptions\WalletNotCreatedException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Debug\ExceptionHandler as LaravelExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ExceptionHandler
{
    public function register(LaravelExceptionHandler $handler): void
    {
        $this->registerValidationExceptions($handler);
        $this->registerAuthExceptions($handler);
        $this->registerNotFoundExceptions($handler);
        $this->registerConflictExceptions($handler);
        $this->registerFallbackException($handler);
    }

    private function registerValidationExceptions(LaravelExceptionHandler $handler): void
    {
        $handler->renderable(fn (ValidationException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage(), 'errors' => $e->errors()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));

        $handler->renderable(fn (InvalidAmountException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));

        $handler->renderable(fn (InsufficientBalanceException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));

        $handler->renderable(fn (DailyLimitExceededException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));

        $handler->renderable(fn (InvalidEmailException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));

        $handler->renderable(fn (InvalidPasswordException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));

        $handler->renderable(fn (InvalidUserNameException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));

        $handler->renderable(fn (SelfTransferNotAllowedException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            $e->getCode()
        ));
    }

    private function registerAuthExceptions(LaravelExceptionHandler $handler): void
    {
        $handler->renderable(fn (AuthenticationException $e, Request $r): JsonResponse => response()->json(
            ['error' => 'Unauthenticated'],
            Response::HTTP_UNAUTHORIZED
        ));

        $handler->renderable(fn (InvalidCredentialsException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_UNAUTHORIZED
        ));
    }

    private function registerNotFoundExceptions(LaravelExceptionHandler $handler): void
    {
        $handler->renderable(fn (WalletNotFoundException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_NOT_FOUND
        ));

        $handler->renderable(fn (WalletNotCreatedException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_NOT_FOUND
        ));

        $handler->renderable(fn (RecipientNotFoundException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_NOT_FOUND
        ));
    }

    private function registerConflictExceptions(LaravelExceptionHandler $handler): void
    {
        $handler->renderable(fn (UserAlreadyExistsException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_CONFLICT
        ));

        $handler->renderable(fn (UserNotExistsException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_NOT_FOUND
        ));

        $handler->renderable(fn (TransferAlreadyProcessedException $e, Request $r): JsonResponse => response()->json(
            ['error' => $e->getMessage()],
            Response::HTTP_CONFLICT
        ));
    }

    private function registerFallbackException(LaravelExceptionHandler $handler): void
    {
        $handler->renderable(function (Throwable $e, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            Log::error('Unhandled exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $exception = InternalServerErrorException::fromException($e);

            return response()->json(
                ['error' => $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        });
    }
}
