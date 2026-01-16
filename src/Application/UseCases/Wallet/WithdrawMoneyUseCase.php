<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\WithdrawMoneyUseCaseInterface;
use App\Application\DTOs\Wallet\WithdrawMoneyDTO;
use App\Application\DTOs\Wallet\WithdrawResultDTO;
use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidAmountException;
use App\Domain\Wallet\Exceptions\TransferAlreadyProcessedException;
use App\Domain\Wallet\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

final readonly class WithdrawMoneyUseCase implements WithdrawMoneyUseCaseInterface
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * @throws InvalidAmountException
     * @throws InsufficientBalanceException
     * @throws TransferAlreadyProcessedException
     */
    public function execute(WithdrawMoneyDTO $dto): WithdrawResultDTO
    {
        if ($this->transactionRepository->idempotencyKeyExists($dto->idempotencyKey)) {
            throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
        }

        $amount = Money::fromDecimal($dto->amount);
        $metadata = array_merge($dto->metadata, ['idempotency_key' => $dto->idempotencyKey]);

        try {
            $aggregate = WalletAggregate::retrieve($dto->walletId)
                ->withdraw($amount->toCents(), $metadata)
                ->persist();
        } catch (UniqueConstraintViolationException) {
            throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
            }
            throw $e;
        }

        $balance = Money::fromCents($aggregate->getBalance(), $aggregate->getCurrency());

        return new WithdrawResultDTO(
            message: 'Withdrawal successful',
            walletId: $dto->walletId,
            balanceCents: $aggregate->getBalance(),
            balance: $balance->toDecimal(),
            currency: $aggregate->getCurrency()
        );
    }
}
