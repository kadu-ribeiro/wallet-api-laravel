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
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Database\QueryException;

final readonly class WithdrawMoneyUseCase implements WithdrawMoneyUseCaseInterface
{
    /**
     * @throws InvalidAmountException
     * @throws InsufficientBalanceException
     * @throws TransferAlreadyProcessedException
     */
    public function execute(WithdrawMoneyDTO $dto): WithdrawResultDTO
    {
        $amount = Money::fromDecimal($dto->amount);
        $metadata = array_merge($dto->metadata, ['idempotency_key' => $dto->idempotencyKey]);

        try {
            $aggregate = WalletAggregate::retrieve($dto->walletId)
                ->withdraw($amount->toCents(), $metadata)
                ->persist();

            $balance = Money::fromCents($aggregate->getBalance(), $aggregate->getCurrency());

            return new WithdrawResultDTO(
                message: 'Withdrawal successful',
                walletId: $dto->walletId,
                balanceCents: $aggregate->getBalance(),
                balance: $balance->toDecimal(),
                currency: $aggregate->getCurrency()
            );
        } catch (QueryException $e) {
            if (intval($e->getCode()) === 23000) {
                throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
            }

            throw $e;
        }
    }
}
