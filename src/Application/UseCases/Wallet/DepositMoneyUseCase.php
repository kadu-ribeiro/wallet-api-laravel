<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\DepositMoneyUseCaseInterface;
use App\Application\DTOs\Wallet\DepositResultDTO;
use App\Application\DTOs\Wallet\DepositMoneyDTO;
use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Domain\Wallet\Exceptions\InvalidAmountException;
use App\Domain\Wallet\Exceptions\TransferAlreadyProcessedException;
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

final readonly class DepositMoneyUseCase implements DepositMoneyUseCaseInterface
{
    public function execute(DepositMoneyDTO $dto): DepositResultDTO
    {
        $amount = Money::fromDecimal($dto->amount);
        $metadata = array_merge($dto->metadata, ['idempotency_key' => $dto->idempotencyKey]);

        try {
            $aggregate = WalletAggregate::retrieve($dto->walletId)
                ->deposit($amount->toCents(), $metadata)
                ->persist()
            ;

            $balance = Money::fromCents($aggregate->getBalance(), $aggregate->getCurrency());

            return new DepositResultDTO(
                message: 'Deposit successful',
                walletId: $dto->walletId,
                balanceCents: $aggregate->getBalance(),
                balance: $balance->toDecimal(),
                currency: $aggregate->getCurrency()
            );
        } catch (InvalidAmountException $e) {
            throw InvalidAmountException::mustBePositive();
        } catch (UniqueConstraintViolationException $e) {
            throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
        } catch (QueryException $e) {
            if (23000 === intval($e->getCode())) {
                throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
            }

            throw $e;
        }
    }
}
