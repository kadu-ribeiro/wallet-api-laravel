<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\GetTransactionHistoryUseCaseInterface;
use App\Domain\Wallet\Queries\TransactionQueryInterface;
use Illuminate\Support\Collection;

final readonly class GetTransactionHistoryUseCase implements GetTransactionHistoryUseCaseInterface
{
    public function __construct(
        private TransactionQueryInterface $transactionQuery
    ) {}

    public function execute(string $walletId, int $perPage = 50): Collection
    {
        return $this->transactionQuery->findByWalletId($walletId, $perPage);
    }
}
