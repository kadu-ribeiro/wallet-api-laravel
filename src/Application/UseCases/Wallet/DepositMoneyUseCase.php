<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\DepositMoneyUseCaseInterface;
use App\Application\DTOs\Wallet\DepositMoneyDTO;
use App\Application\DTOs\Wallet\DepositResultDTO;
use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Domain\Wallet\Exceptions\TransferAlreadyProcessedException;
use App\Domain\Wallet\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;

final readonly class DepositMoneyUseCase implements DepositMoneyUseCaseInterface
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    public function execute(DepositMoneyDTO $dto): DepositResultDTO
    {
        if ($this->transactionRepository->idempotencyKeyExists($dto->idempotencyKey)) {
            throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
        }

        $amount = Money::fromDecimal($dto->amount);
        $metadata = array_merge($dto->metadata, ['idempotency_key' => $dto->idempotencyKey]);

        $aggregate = WalletAggregate::retrieve($dto->walletId)
            ->deposit($amount->toCents(), $metadata)
            ->persist();

        $balance = Money::fromCents($aggregate->getBalance(), $aggregate->getCurrency());

        return new DepositResultDTO(
            message: 'Deposit successful',
            walletId: $dto->walletId,
            balanceCents: $aggregate->getBalance(),
            balance: $balance->toDecimal(),
            currency: $aggregate->getCurrency()
        );
    }
}
