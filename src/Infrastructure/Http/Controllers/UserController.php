<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\DTOs\User\CreateUserDTO;
use App\Application\UseCases\User\CreateUserUseCase;
use App\Application\UseCases\User\GetCurrentUserUseCase;
use App\Application\UseCases\User\GetUserByIdUseCase;
use App\Application\UseCases\User\LoginUserUseCase;
use App\Infrastructure\Http\Requests\RegisterUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly LoginUserUseCase $loginUserUseCase,
        private readonly GetCurrentUserUseCase $getCurrentUserUseCase,
        private readonly GetUserByIdUseCase $getUserByIdUseCase
    ) {}

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $result = $this->createUserUseCase->execute(CreateUserDTO::fromRequest($request));

        return response()->json($result->toArray(), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $result = $this->loginUserUseCase->execute(
            email: (string) $request->input('email', ''),
            password: (string) $request->input('password', '')
        );

        return response()->json($result->toArray());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->getCurrentUserUseCase->execute()->toArray()]);
    }

    public function showById(string $userId): JsonResponse
    {
        return response()->json(['data' => $this->getUserByIdUseCase->execute($userId)->toArray()]);
    }
}
