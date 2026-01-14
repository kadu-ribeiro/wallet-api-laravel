<?php

test('InvalidCredentialsException has correct message', function () {
    $exception = \App\Domain\User\Exceptions\InvalidCredentialsException::create();
    
    expect($exception->getMessage())->toBe('Invalid email or password');
});

test('WalletNotFoundException contains wallet ID', function () {
    $id = 'wallet-123';
    $exception = \App\Domain\Wallet\Exceptions\WalletNotFoundException::withId($id);
    
    expect($exception->getMessage())->toContain($id);
});

test('InsufficientBalanceException shows required and available amounts', function () {
    $exception = \App\Domain\Wallet\Exceptions\InsufficientBalanceException::forWithdrawal(
        balanceCents: 5000,
        requestedCents: 10000
    );
    
    expect($exception->getMessage())->toContain('50.00');
    expect($exception->getMessage())->toContain('100.00');
});

test('SelfTransferNotAllowedException returns 422', function () {
    $exception = \App\Domain\Wallet\Exceptions\SelfTransferNotAllowedException::create();
    
    expect($exception->getMessage())->toBe('You cannot transfer money to yourself');
    expect($exception->getCode())->toBe(422);
});

test('UserNotExistsException contains identifier', function () {
    $email = 'test@test.com';
    $exception = \App\Domain\User\Exceptions\UserNotExistsException::withEmail($email);
    
    expect($exception->getMessage())->toContain($email);
});

test('RecipientNotFoundException contains email', function () {
    $email = 'test@test.com';
    $exception = \App\Domain\Wallet\Exceptions\RecipientNotFoundException::withEmail($email);
    
    expect($exception->getMessage())->toContain($email);
});

test('InvalidAmountException has correct message', function () {
    $exception = \App\Domain\Wallet\Exceptions\InvalidAmountException::mustBePositive();
    
    expect($exception->getMessage())->toBe('Amount must be positive');
});

test('UserHasNoWalletException returns 404', function () {
    $exception = \App\Domain\User\Exceptions\UserHasNoWalletException::create();
    
   expect($exception->getMessage())->toBe('User has no wallet associated');
    expect($exception->getCode())->toBe(404);
});
