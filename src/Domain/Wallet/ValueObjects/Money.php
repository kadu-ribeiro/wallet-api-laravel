<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

use Brick\Math\RoundingMode;
use Brick\Money\Context\CashContext;
use Brick\Money\Money as BrickMoney;
use InvalidArgumentException;

final readonly class Money
{
    private function __construct(
        private BrickMoney $money
    ) {}

    public static function fromCents(int $amountCents, string $currencyCode = 'BRL'): self
    {
        if ($amountCents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        return new self(
            BrickMoney::ofMinor($amountCents, $currencyCode, new CashContext(1), RoundingMode::HALF_UP)
        );
    }

    public static function fromDecimal(string $amount, string $currencyCode = 'BRL'): self
    {
        if (str_starts_with($amount, '-')) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        return new self(
            BrickMoney::of($amount, $currencyCode, new CashContext(1), RoundingMode::HALF_UP)
        );
    }

    public static function zero(string $currencyCode = 'BRL'): self
    {
        return self::fromCents(0, $currencyCode);
    }

    public function toCents(): int
    {
        return (int) $this->money->getMinorAmount()->toInt();
    }

    public function toDecimal(): string
    {
        return $this->money->getAmount()->toScale(2, RoundingMode::HALF_UP)->__toString();
    }

    public function getCurrency(): string
    {
        return $this->money->getCurrency()->getCurrencyCode();
    }

    public function add(self $other): self
    {
        return new self($this->money->plus($other->money));
    }

    public function subtract(self $other): self
    {
        return new self($this->money->minus($other->money));
    }

    public function multiply(float|int $multiplier): self
    {
        return new self($this->money->multipliedBy($multiplier));
    }

    public function greaterThan(self $other): bool
    {
        return $this->money->isGreaterThan($other->money);
    }

    public function greaterThanOrEqual(self $other): bool
    {
        return $this->money->isGreaterThanOrEqualTo($other->money);
    }

    public function lessThan(self $other): bool
    {
        return $this->money->isLessThan($other->money);
    }

    public function equals(self $other): bool
    {
        return $this->money->isEqualTo($other->money);
    }

    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    public function isPositive(): bool
    {
        return $this->money->isPositive();
    }

    public function format(): string
    {
        return $this->money->formatTo('pt_BR');
    }
}
