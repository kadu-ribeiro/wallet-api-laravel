<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\DTOs\Wallet\DepositMoneyDTO;
use App\Application\DTOs\Wallet\WithdrawMoneyDTO;
use App\Application\UseCases\Wallet\DepositMoneyUseCase;
use App\Application\UseCases\Wallet\GetTransactionHistoryUseCase;
use App\Application\UseCases\Wallet\GetWalletBalanceUseCase;
use App\Application\UseCases\Wallet\WithdrawMoneyUseCase;
use App\Domain\User\Exceptions\UserHasNoWalletException;
use App\Domain\User\Services\AuthContextInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Infrastructure\Http\Requests\DepositRequest;
use App\Infrastructure\Http\Requests\WithdrawRequest;
use App\Infrastructure\Persistence\Eloquent\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly GetWalletBalanceUseCase $getWalletBalanceUseCase,
        private readonly GetTransactionHistoryUseCase $getTransactionHistoryUseCase,
        private readonly DepositMoneyUseCase $depositMoneyUseCase,
        private readonly WithdrawMoneyUseCase $withdrawMoneyUseCase,
        private readonly AuthContextInterface $authContext,
        private readonly WalletRepositoryInterface $walletRepository
    ) {}

    private function getAuthenticatedWalletId(): string
    {
        $userId = $this->authContext->getUserId();
        $wallet = $this->walletRepository->findByUserId($userId);

        if (! $wallet) {
            throw UserHasNoWalletException::create();
        }

        return $wallet->id->value;
    }

    public function show(): JsonResponse
    {
        $walletId = $this->getAuthenticatedWalletId();
        $wallet = $this->getWalletBalanceUseCase->execute($walletId);

        return response()->json(['data' => $wallet->toArray()]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $walletId = $this->getAuthenticatedWalletId();
        $perPage = (int) $request->query('per_page', 50);
        $transactions = $this->getTransactionHistoryUseCase->execute($walletId, $perPage);

        return response()->json(
            $transactions->map(fn ($tx) => $tx->toArray())->all()
        );
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        $walletId = $this->getAuthenticatedWalletId();
        $result = $this->depositMoneyUseCase->execute(
            DepositMoneyDTO::fromPrimitives(
                walletId: $walletId,
                amount: $request->amount(),
                idempotencyKey: $request->idempotencyKey(),
                metadata: $request->metadata()
            )
        );

        return response()->json($result->toArray());
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $walletId = $this->getAuthenticatedWalletId();
        $result = $this->withdrawMoneyUseCase->execute(
            WithdrawMoneyDTO::fromPrimitives(
                walletId: $walletId,
                amount: $request->amount(),
                idempotencyKey: $request->idempotencyKey(),
                metadata: $request->metadata()
            )
        );

        return response()->json($result->toArray());
    }

    public function showById(string $walletId): JsonResponse
    {
        $wallet = $this->getWalletBalanceUseCase->execute($walletId);

        return response()->json(['data' => $wallet->toArray()]);
    }

    public function transactionsById(string $walletId, Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 50);
        $transactions = $this->getTransactionHistoryUseCase->execute($walletId, $perPage);

        return response()->json(
            $transactions->map(fn ($tx) => $tx->toArray())->all()
        );
    }

    public function index(): JsonResponse
    {
        $wallets = Wallet::all();

        return response()->json([
            'data' => $wallets->map(fn ($w) => [
                'id' => $w->id,
                'user_id' => $w->user_id,
                'balance' => number_format($w->balance_cents / 100, 2, '.', ''),
                'balance_cents' => $w->balance_cents,
                'currency' => $w->currency,
                'created_at' => $w->created_at->toIso8601String(),
            ])->all(),
        ]);
    }
}
