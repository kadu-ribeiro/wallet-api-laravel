<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Aggregates;

use App\Domain\Wallet\Events\MoneyDeposited;
use App\Domain\Wallet\Events\MoneyTransferredIn;
use App\Domain\Wallet\Events\MoneyTransferredOut;
use App\Domain\Wallet\Events\MoneyWithdrawn;
use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Exceptions\DailyLimitExceededException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidAmountException;
use App\Domain\Wallet\Exceptions\InvalidIdempotencyKeyException;
use App\Domain\Wallet\Exceptions\WalletNotCreatedException;
use Carbon\Carbon;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

final class WalletAggregate extends AggregateRoot
{
    private int $balanceCents = 0;

    private string $currency = 'BRL';

    private string $userId = '';

    private bool $isCreated = false;

    private ?Carbon $lastTransactionDate = null;

    private int $dailyWithdrawalAmount = 0;

    private int $dailyTransferOutAmount = 0;

    public function recordThat(ShouldBeStored $domainEvent): static
    {
        parent::recordThat($domainEvent);

        $frequency = config('wallet.snapshot_frequency', 100);
        if ($this->aggregateVersion > 0 && 0 === $this->aggregateVersion % $frequency) {
            $this->snapshot();
        }

        return $this;
    }

    public function createWallet(string $userId, string $currency = 'BRL'): self
    {
        $this->recordThat(new WalletCreated(
            walletId: $this->uuid(),
            userId: $userId,
            currency: $currency
        ));

        return $this;
    }

    /**
     * @throws InvalidAmountException
     */
    public function deposit(int $amountCents, array $metadata = []): self
    {
        $this->ensureWalletExists();
        $this->ensureAmountIsPositive($amountCents);

        $newBalance = $this->balanceCents + $amountCents;

        $idempotencyKey = $metadata['idempotency_key'] ?? throw new InvalidIdempotencyKeyException();
        unset($metadata['idempotency_key']);

        $this->recordThat(new MoneyDeposited(
            walletId: $this->uuid(),
            amountCents: $amountCents,
            balanceAfterCents: $newBalance,
            idempotencyKey: $idempotencyKey,
            metadata: $metadata
        ))->snapshot();

        return $this;
    }

    /**
     * @throws InvalidAmountException
     * @throws InsufficientBalanceException
     */
    public function withdraw(int $amountCents, array $metadata = []): self
    {
        $this->ensureWalletExists();
        $this->ensureAmountIsPositive($amountCents);
        $this->ensureSufficientBalance($amountCents);
        $this->ensureDailyWithdrawalLimitNotExceeded($amountCents);

        $newBalance = $this->balanceCents - $amountCents;

        $idempotencyKey = $metadata['idempotency_key'] ?? throw new InvalidIdempotencyKeyException();
        unset($metadata['idempotency_key']);

        $this->recordThat(new MoneyWithdrawn(
            walletId: $this->uuid(),
            amountCents: $amountCents,
            balanceAfterCents: $newBalance,
            idempotencyKey: $idempotencyKey,
            metadata: $metadata
        ));

        return $this;
    }

    /**
     * @throws InvalidAmountException
     * @throws InsufficientBalanceException
     */
    public function transferOut(
        int $amountCents,
        string $recipientEmail,
        string $transferId,
        array $metadata = []
    ): self {
        $this->ensureWalletExists();
        $this->ensureAmountIsPositive($amountCents);
        $this->ensureSufficientBalance($amountCents);
        $this->ensureDailyTransferLimitNotExceeded($amountCents);

        $newBalance = $this->balanceCents - $amountCents;

        $this->recordThat(new MoneyTransferredOut(
            walletId: $this->uuid(),
            amountCents: $amountCents,
            balanceAfterCents: $newBalance,
            recipientEmail: $recipientEmail,
            transferId: $transferId,
            metadata: $metadata
        ));

        return $this;
    }

    /**
     * @throws InvalidAmountException
     */
    public function transferIn(
        int $amountCents,
        string $senderEmail,
        string $transferId,
        array $metadata = []
    ): self {
        $this->ensureWalletExists();
        $this->ensureAmountIsPositive($amountCents);

        $newBalance = $this->balanceCents + $amountCents;

        $this->recordThat(new MoneyTransferredIn(
            walletId: $this->uuid(),
            amountCents: $amountCents,
            balanceAfterCents: $newBalance,
            senderEmail: $senderEmail,
            transferId: $transferId,
            metadata: $metadata
        ));

        return $this;
    }

    public function getBalance(): int
    {
        return $this->balanceCents;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    // Event Appliers
    protected function applyWalletCreated(WalletCreated $event): void
    {
        $this->isCreated = true;
        $this->userId = $event->userId;
        $this->currency = $event->currency;
        $this->balanceCents = 0;
    }

    protected function applyMoneyDeposited(MoneyDeposited $event): void
    {
        $this->balanceCents = $event->balanceAfterCents;
    }

    protected function applyMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        $this->balanceCents = $event->balanceAfterCents;
        $this->updateDailyWithdrawalAmount($event->amountCents);
    }

    protected function applyMoneyTransferredOut(MoneyTransferredOut $event): void
    {
        $this->balanceCents = $event->balanceAfterCents;
        $this->updateDailyTransferOutAmount($event->amountCents);
    }

    protected function applyMoneyTransferredIn(MoneyTransferredIn $event): void
    {
        $this->balanceCents = $event->balanceAfterCents;
    }

    protected function getState(): array
    {
        return [
            'balanceCents' => $this->balanceCents,
            'currency' => $this->currency,
            'userId' => $this->userId,
            'isCreated' => $this->isCreated,
            'dailyWithdrawalAmount' => $this->dailyWithdrawalAmount,
            'dailyTransferOutAmount' => $this->dailyTransferOutAmount,
            'lastTransactionDate' => $this->lastTransactionDate?->toDateString(),
        ];
    }

    protected function useState(array $state): void
    {
        $this->balanceCents = $state['balanceCents'];
        $this->currency = $state['currency'];
        $this->userId = $state['userId'];
        $this->isCreated = $state['isCreated'];
        $this->dailyWithdrawalAmount = $state['dailyWithdrawalAmount'] ?? 0;
        $this->dailyTransferOutAmount = $state['dailyTransferOutAmount'] ?? 0;
        $this->lastTransactionDate = isset($state['lastTransactionDate'])
            ? Carbon::parse($state['lastTransactionDate'])
            : null;
    }

    // Guards
    private function ensureWalletExists(): void
    {
        if (! $this->isCreated) {
            throw WalletNotCreatedException::create();
        }
    }

    private function ensureAmountIsPositive(int $amountCents): void
    {
        if ($amountCents <= 0) {
            throw InvalidAmountException::mustBePositive();
        }
    }

    private function ensureSufficientBalance(int $amountCents): void
    {
        if ($this->balanceCents < $amountCents) {
            throw InsufficientBalanceException::forWithdrawal(
                $this->balanceCents,
                $amountCents
            );
        }
    }

    private function ensureDailyWithdrawalLimitNotExceeded(int $amountCents): void
    {
        $todayUTC = Carbon::now('UTC')->startOfDay();
        $currentUsage = $this->isTransactionToday($todayUTC) ? $this->dailyWithdrawalAmount : 0;
        $limit = config('wallet.daily_limits.withdrawal');

        if (($currentUsage + $amountCents) > $limit) {
            throw DailyLimitExceededException::forWithdrawal($currentUsage, $limit, $amountCents);
        }
    }

    private function ensureDailyTransferLimitNotExceeded(int $amountCents): void
    {
        $todayUTC = Carbon::now('UTC')->startOfDay();
        $currentUsage = $this->isTransactionToday($todayUTC) ? $this->dailyTransferOutAmount : 0;
        $limit = config('wallet.daily_limits.transfer');

        if (($currentUsage + $amountCents) > $limit) {
            throw DailyLimitExceededException::forTransfer($currentUsage, $limit, $amountCents);
        }
    }

    private function isTransactionToday(Carbon $todayUTC): bool
    {
        if (null === $this->lastTransactionDate) {
            return false;
        }

        return $this->lastTransactionDate->isSameDay($todayUTC);
    }

    private function updateDailyUsage(string $propertyName, int $amountCents): void
    {
        $todayUTC = Carbon::now('UTC')->startOfDay();

        if (! $this->isTransactionToday($todayUTC)) {
            $this->{$propertyName} = $amountCents;
        } else {
            $this->{$propertyName} += $amountCents;
        }

        $this->lastTransactionDate = $todayUTC;
    }

    private function updateDailyWithdrawalAmount(int $amountCents): void
    {
        $this->updateDailyUsage('dailyWithdrawalAmount', $amountCents);
    }

    private function updateDailyTransferOutAmount(int $amountCents): void
    {
        $this->updateDailyUsage('dailyTransferOutAmount', $amountCents);
    }
}
