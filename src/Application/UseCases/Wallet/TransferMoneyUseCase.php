<?php

declare(strict_types=1);

namespace App\Application\UseCases\Wallet;

use App\Application\Contracts\Wallet\TransferMoneyUseCaseInterface;
use App\Application\DTOs\Wallet\TransferMoneyDTO;
use App\Application\DTOs\Wallet\TransferResultDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Aggregates\WalletAggregate;
use App\Domain\Wallet\Exceptions\RecipientNotFoundException;
use App\Domain\Wallet\Exceptions\SelfTransferNotAllowedException;
use App\Domain\Wallet\Exceptions\TransferAlreadyProcessedException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\EventSourcing\Projectionist;

final readonly class TransferMoneyUseCase implements TransferMoneyUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepositoryInterface $walletRepository,
        private Projectionist $projectionist
    ) {}

    public function execute(TransferMoneyDTO $dto): TransferResultDTO
    {
        $this->walletRepository->findById(new WalletId($dto->walletId))
        ?? throw WalletNotFoundException::withId($dto->walletId);

        $recipientEmail = Email::from($dto->recipientEmail);
        $amount = Money::fromDecimal($dto->amount);

        $recipient = $this->userRepository->findByEmail($recipientEmail)
            ?? throw RecipientNotFoundException::withEmail($dto->recipientEmail);

        $recipientData = $recipient->toArray();
        $recipientWallet = $this->walletRepository->findByUserId(new UserId($recipientData['id']))
            ?? throw RecipientNotFoundException::walletNotFound($dto->recipientEmail);

        if ($dto->walletId === $recipientWallet->id->value) {
            throw SelfTransferNotAllowedException::create();
        }

        $senderAggregate = WalletAggregate::retrieve($dto->walletId)
            ->transferOut(
                amountCents: $amount->toCents(),
                recipientEmail: $dto->recipientEmail,
                transferId: $dto->idempotencyKey,
                metadata: $dto->metadata
            );

        $recipientAggregate = WalletAggregate::retrieve($recipientWallet->id->value)
            ->transferIn(
                amountCents: $amount->toCents(),
                senderEmail: $dto->userEmail,
                transferId: $dto->idempotencyKey,
                metadata: $dto->metadata
            );

        try {
            $storedEvents = DB::transaction(function () use ($senderAggregate, $recipientAggregate) {
                $senderEvents = $senderAggregate->persistWithoutApplyingToEventHandlers()->all();
                $recipientEvents = $recipientAggregate->persistWithoutApplyingToEventHandlers()->all();

                return [...$senderEvents, ...$recipientEvents];
            });
        } catch (QueryException $e) {
            if (23000 === (int) $e->getCode()) {
                throw TransferAlreadyProcessedException::withIdempotencyKey($dto->idempotencyKey);
            }

            throw $e;
        }

        $this->projectionist->handleStoredEvents(collect($storedEvents));

        $senderBalance = Money::fromCents($senderAggregate->getBalance(), $senderAggregate->getCurrency());
        $recipientBalance = Money::fromCents($recipientAggregate->getBalance(), $recipientAggregate->getCurrency());

        return new TransferResultDTO(
            message: 'Transfer successful',
            transferId: $dto->idempotencyKey,
            walletId: $dto->walletId,
            balanceCents: $senderAggregate->getBalance(),
            balance: $senderBalance->toDecimal(),
            recipientWalletId: $recipientWallet->id->value,
            recipientBalanceCents: $recipientAggregate->getBalance(),
            recipientBalance: $recipientBalance->toDecimal(),
            amountCents: $amount->toCents(),
            amount: $amount->toDecimal(),
            currency: $senderAggregate->getCurrency()
        );
    }
}
