<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\DTOs\Wallet\TransferMoneyDTO;
use App\Application\UseCases\Wallet\TransferMoneyUseCase;
use App\Infrastructure\Http\Requests\TransferRequest;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function __construct(
        private readonly TransferMoneyUseCase $transferMoneyUseCase
    ) {}

    public function store(TransferRequest $request): JsonResponse
    {
        $result = $this->transferMoneyUseCase->execute(TransferMoneyDTO::fromRequest($request));

        return response()->json($result->toArray());
    }
}
