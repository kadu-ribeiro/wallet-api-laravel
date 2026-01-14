<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\DTOs\Wallet\DepositMoneyDTO;
use App\Application\DTOs\Wallet\WithdrawMoneyDTO;
use App\Application\UseCases\Wallet\DepositMoneyUseCase;
use App\Application\UseCases\Wallet\GetTransactionHistoryUseCase;
use App\Application\UseCases\Wallet\GetWalletBalanceUseCase;
use App\Application\UseCases\Wallet\GetWalletByUserIdUseCase;
use App\Application\UseCases\Wallet\WithdrawMoneyUseCase;
use App\Infrastructure\Http\Requests\DepositRequest;
use App\Infrastructure\Http\Requests\WithdrawRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly GetWalletBalanceUseCase $getWalletBalanceUseCase,
        private readonly GetTransactionHistoryUseCase $getTransactionHistoryUseCase,
        private readonly DepositMoneyUseCase $depositMoneyUseCase,
        private readonly WithdrawMoneyUseCase $withdrawMoneyUseCase
    ) {}

    public function show(string $walletId): JsonResponse
    {
        $wallet = $this->getWalletBalanceUseCase->execute($walletId);

        return response()->json(['data' => $wallet->toArray()]);
    }

    public function balance(string $walletId): JsonResponse
    {
        $wallet = $this->getWalletBalanceUseCase->execute($walletId);

        return response()->json(['data' => $wallet->toArray()]);
    }

    public function transactions(string $walletId, Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 50);
        $transactions = $this->getTransactionHistoryUseCase->execute($walletId, $perPage);

        return response()->json(
            $transactions->map(fn ($tx) => $tx->toArray())->all()
        );
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        $result = $this->depositMoneyUseCase->execute(DepositMoneyDTO::fromRequest($request));

        return response()->json($result->toArray());
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $result = $this->withdrawMoneyUseCase->execute(WithdrawMoneyDTO::fromRequest($request));

        return response()->json($result->toArray());
    }

    public function getByUserId(string $userId, GetWalletByUserIdUseCase $useCase): JsonResponse
    {
        $wallet = $useCase->execute($userId);

        return response()->json(['data' => $wallet->toArray()]);
    }
}
