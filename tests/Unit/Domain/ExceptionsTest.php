<?php

declare(strict_types=1);

use App\Domain\User\Exceptions\InvalidCredentialsException;
use App\Domain\User\Exceptions\UserHasNoWalletException;
use App\Domain\User\Exceptions\UserNotExistsException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidAmountException;
use App\Domain\Wallet\Exceptions\RecipientNotFoundException;
use App\Domain\Wallet\Exceptions\SelfTransferNotAllowedException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;

test('InvalidCredentialsException has correct message', function (): void {
    $exception = InvalidCredentialsException::create();

    expect($exception->getMessage())->toBe('Invalid email or password');
});

test('WalletNotFoundException contains wallet ID', function (): void {
    $id = 'wallet-123';
    $exception = WalletNotFoundException::withId($id);

    expect($exception->getMessage())->toContain($id);
});

test('InsufficientBalanceException shows required and available amounts', function (): void {
    $exception = InsufficientBalanceException::forWithdrawal(
        balanceCents: 5000,
        requestedCents: 10000
    );

    expect($exception->getMessage())->toContain('50.00');
    expect($exception->getMessage())->toContain('100.00');
});

test('SelfTransferNotAllowedException returns 422', function (): void {
    $exception = SelfTransferNotAllowedException::create();

    expect($exception->getMessage())->toBe('You cannot transfer money to yourself');
    expect($exception->getCode())->toBe(422);
});

test('UserNotExistsException contains identifier', function (): void {
    $email = 'test@test.com';
    $exception = UserNotExistsException::withEmail($email);

    expect($exception->getMessage())->toContain($email);
});

test('RecipientNotFoundException contains email', function (): void {
    $email = 'test@test.com';
    $exception = RecipientNotFoundException::withEmail($email);

    expect($exception->getMessage())->toContain($email);
});

test('InvalidAmountException has correct message', function (): void {
    $exception = InvalidAmountException::mustBePositive();

    expect($exception->getMessage())->toBe('Amount must be positive');
});

test('UserHasNoWalletException returns 404', function (): void {
    $exception = UserHasNoWalletException::create();

    expect($exception->getMessage())->toBe('User has no wallet associated');
    expect($exception->getCode())->toBe(404);
});
