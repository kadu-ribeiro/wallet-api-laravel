<?php

declare(strict_types=1);

use App\Domain\User\Exceptions\InvalidEmailException;
use App\Domain\User\Exceptions\InvalidPasswordException;
use App\Domain\User\Exceptions\InvalidUserNameException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidAmountException;

test('InvalidEmailException has correct message', function (): void {
    $exception = InvalidEmailException::empty();
    expect($exception->getMessage())->toContain('Email cannot be empty');
});

test('InvalidPasswordException for empty password', function (): void {
    $exception = InvalidPasswordException::empty();
    expect($exception->getMessage())->toContain('Password cannot be empty');
});

test('InvalidPasswordException for short password', function (): void {
    $exception = InvalidPasswordException::tooShort(8);
    expect($exception->getMessage())->toContain('at least 8 characters');
});

test('InvalidUserNameException for empty name', function (): void {
    $exception = InvalidUserNameException::empty();
    expect($exception->getMessage())->toContain('User name cannot be empty');
});

test('InvalidUserNameException for too short', function (): void {
    $exception = InvalidUserNameException::tooShort(2);
    expect($exception->getMessage())->toContain('at least 2 characters');
});

test('InvalidAmountException must be positive', function (): void {
    $exception = InvalidAmountException::mustBePositive();
    expect($exception->getMessage())->toContain('Amount must be positive');
});

test('InsufficientBalanceException shows amounts', function (): void {
    $exception = InsufficientBalanceException::forWithdrawal(10000, 15000);
    expect($exception->getMessage())
        ->toContain('100.00')
        ->toContain('150.00');
});
