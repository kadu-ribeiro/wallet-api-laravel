<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\WithdrawMoneyUseCaseInterface;
use App\Application\DTOs\Wallet\WithdrawMoneyDTO;
use App\Application\DTOs\Wallet\WithdrawResultDTO;
use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Domain\Wallet\Exceptions\TransferAlreadyProcessedException;
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

final readonly class WithdrawMoneyUseCase implements WithdrawMoneyUseCaseInterface
{
    public function execute(WithdrawMoneyDTO $dto): WithdrawResultDTO
    {
        $amount = Money::fromDecimal($dto->amount);
        $metadata = array_merge($dto->metadata, ['idempotency_key' => $dto->idempotencyKey]);

        try {
            $aggregate = WalletAggregate::retrieve($dto->walletId)
                ->withdraw($amount->toCents(), $metadata)
                ->persist()
            ;

            $balance = Money::fromCents($aggregate->getBalance(), $aggregate->getCurrency());

            return new WithdrawResultDTO(
                message: 'Withdrawal successful',
                walletId: $dto->walletId,
                balanceCents: $aggregate->getBalance(),
                balance: $balance->toDecimal(),
                currency: $aggregate->getCurrency()
            );
        } catch (UniqueConstraintViolationException $e) {
            throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
        } catch (QueryException $e) {
            if ('23000' === $e->getCode() || ($e->errorInfo[1] ?? 0) === 1062) {
                throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
            }

            throw $e;
        }
    }
}
