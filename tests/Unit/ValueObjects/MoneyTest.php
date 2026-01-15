<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\Money;

test('money fromCents creates correct value', function (): void {
    $money = Money::fromCents(10050, 'BRL');

    expect($money->toCents())->toBe(10050)
        ->and($money->toDecimal())->toBe('100.50')
        ->and($money->getCurrency())->toBe('BRL');
});

test('money fromDecimal creates correct value', function (): void {
    $money = Money::fromDecimal('250.76', 'BRL');

    expect($money->toCents())->toBe(25076)
        ->and($money->getCurrency())->toBe('BRL');
});

test('money throws on negative amount', function (): void {
    Money::fromDecimal('-10.00', 'BRL');
})->throws(InvalidArgumentException::class, 'Amount cannot be negative');

test('money arithmetic add works', function (): void {
    $money1 = Money::fromDecimal('100.00', 'BRL');
    $money2 = Money::fromDecimal('50.00', 'BRL');
    $result = $money1->add($money2);

    expect($result->toDecimal())->toBe('150.00')
        ->and($result->toCents())->toBe(15000);
});

test('money arithmetic subtract works', function (): void {
    $money1 = Money::fromDecimal('100.00', 'BRL');
    $money2 = Money::fromDecimal('30.00', 'BRL');
    $result = $money1->subtract($money2);

    expect($result->toDecimal())->toBe('70.00')
        ->and($result->toCents())->toBe(7000);
});

test('money zero works', function (): void {
    $money = Money::zero('BRL');

    expect($money->toCents())->toBe(0)
        ->and($money->toDecimal())->toBe('0.00')
        ->and($money->isZero())->toBeTrue();
});

test('money isPositive works', function (): void {
    $positive = Money::fromDecimal('10.00');

    expect($positive->isPositive())->toBeTrue();
});
