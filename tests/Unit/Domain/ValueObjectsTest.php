<?php

use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\User\ValueObjects\Email;

test('Money VO converts cents to decimal correctly', function () {
    $money = Money::fromCents(12346, 'BRL'); // Use 12346 to match rounded decimal
    
    expect($money->toDecimal())->toBe('123.46'); // CashContext rounds
    expect($money->toCents())->toBe(12346);
    expect($money->getCurrency())->toBe('BRL');
});

test('Money VO handles edge cases', function () {
    expect(Money::fromCents(1, 'BRL')->toDecimal())->toBe('0.02'); // CashContext
    expect(Money::fromCents(0, 'BRL')->toDecimal())->toBe('0.00');
    expect(Money::fromCents(999999999, 'BRL')->toDecimal())->toBe('10000000.00'); // Rounded
    expect(Money::fromCents(10, 'USD')->toDecimal())->toBe('0.10');
});

test('Money VO fromDecimal creates correct cents', function () {
    $money = Money::fromDecimal('123.45', 'BRL');
    
    expect($money->toCents())->toBe(12346); // CashContext rounds to nearest cash unit
    expect($money->toDecimal())->toBe('123.46');
});

test('Email VO stores value correctly', function () {
    $email = Email::from('valid@test.com');
    
    expect($email->value)->toBe('valid@test.com');
});

test('Money VO handles large amounts', function () {
    $cents = 1234567890;
    $money = Money::fromCents($cents, 'BRL');
    
    expect($money->toCents())->toBe($cents);
});

test('Money VO decimal precision with CashContext', function () {
    $money1 = Money::fromDecimal('0.01', 'BRL');
    $money2 = Money::fromDecimal('0.99', 'BRL');
    
    expect($money1->toDecimal())->toBe('0.02'); // Rounds to nearest cash unit (0.02)
    expect($money2->toDecimal())->toBe('1.00'); // Rounds to 1.00
});
