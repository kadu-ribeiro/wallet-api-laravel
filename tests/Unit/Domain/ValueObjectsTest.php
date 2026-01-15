<?php

declare(strict_types=1);

use App\Domain\User\ValueObjects\Email;
use App\Domain\Wallet\ValueObjects\Money;

test('Money VO converts cents to decimal correctly', function (): void {
    $money = Money::fromCents(12345, 'BRL');

    expect($money->toDecimal())->toBe('123.45');
    expect($money->toCents())->toBe(12345);
    expect($money->getCurrency())->toBe('BRL');
});

test('Money VO handles edge cases', function (): void {
    expect(Money::fromCents(1, 'BRL')->toDecimal())->toBe('0.01');
    expect(Money::fromCents(0, 'BRL')->toDecimal())->toBe('0.00');
    expect(Money::fromCents(999999999, 'BRL')->toDecimal())->toBe('9999999.99');
    expect(Money::fromCents(10, 'USD')->toDecimal())->toBe('0.10');
});

test('Money VO fromDecimal creates correct cents', function (): void {
    $money = Money::fromDecimal('123.45', 'BRL');

    expect($money->toCents())->toBe(12345);
    expect($money->toDecimal())->toBe('123.45');
});

test('Email VO stores value correctly', function (): void {
    $email = Email::from('valid@test.com');

    expect($email->value)->toBe('valid@test.com');
});

test('Money VO handles large amounts', function (): void {
    $cents = 1234567890;
    $money = Money::fromCents($cents, 'BRL');

    expect($money->toCents())->toBe($cents);
});

test('Money VO decimal precision is exact to cents', function (): void {
    $money1 = Money::fromDecimal('0.01', 'BRL');
    $money2 = Money::fromDecimal('0.99', 'BRL');
    $money3 = Money::fromDecimal('10.27', 'BRL');
    $money4 = Money::fromDecimal('93.99', 'BRL');

    expect($money1->toCents())->toBe(1);
    expect($money1->toDecimal())->toBe('0.01');
    expect($money2->toCents())->toBe(99);
    expect($money2->toDecimal())->toBe('0.99');
    expect($money3->toCents())->toBe(1027);
    expect($money4->toCents())->toBe(9399);
});
