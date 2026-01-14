<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use DomainException;

final class DailyLimitExceededException extends DomainException
{
    public static function forDeposit(int $currentTotal, int $limit, int $attemptedAmount): self
    {
        return new self(sprintf(
            'Daily deposit limit exceeded. Current: R$ %.2f, Limit: R$ %.2f, Attempted: R$ %.2f',
            $currentTotal / 100,
            $limit / 100,
            $attemptedAmount / 100
        ));
    }

    public static function forWithdrawal(int $currentTotal, int $limit, int $attemptedAmount): self
    {
        return new self(sprintf(
            'Daily withdrawal limit exceeded. Current: R$ %.2f, Limit: R$ %.2f, Attempted: R$ %.2f',
            $currentTotal / 100,
            $limit / 100,
            $attemptedAmount / 100
        ));
    }

    public static function forTransfer(int $currentTotal, int $limit, int $attemptedAmount): self
    {
        return new self(sprintf(
            'Daily transfer limit exceeded. Current: R$ %.2f, Limit: R$ %.2f, Attempted: R$ %.2f',
            $currentTotal / 100,
            $limit / 100,
            $attemptedAmount / 100
        ));
    }
}
