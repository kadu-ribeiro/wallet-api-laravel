<?php

use App\Domain\User\DTOs\UserDTO;
use App\Domain\Wallet\DTOs\{WalletDTO, TransactionDTO};
use Illuminate\Support\Str;

test('UserDTO toArray has all required fields', function () {
    $userId = Str::orderedUuid()->toString();
    $walletId = Str::orderedUuid()->toString();
    
    $dto = UserDTO::fromPrimitives(
        id: $userId,
        name: 'Test User',
        email: 'test@test.com',
        walletId: $walletId,
        createdAt: '2024-01-01T00:00:00Z'
    );
    
    $array = $dto->toArray();
    
    expect($array)->toHaveKeys(['id', 'name', 'email', 'wallet_id', 'created_at']);
    expect($array['id'])->toBe($userId);
    expect($array['wallet_id'])->toBe($walletId);
});

test('WalletDTO has balance and balance_cents', function () {
    $dto = WalletDTO::fromPrimitives(
        id: Str::orderedUuid()->toString(),
        userId: Str::orderedUuid()->toString(),
        balanceCents: 10000,
        currency: 'BRL',
        createdAt: '2024-01-01T00:00:00Z'
    );
    
    $array = $dto->toArray();
    
    expect($array['balance'])->toBeString();
    expect($array['balance_cents'])->toBe(10000);
    expect($array['currency'])->toBe('BRL');
});

test('TransactionDTO formats amounts correctly', function () {
    $dto = TransactionDTO::fromPrimitives(
        id: 1,
        walletId: Str::orderedUuid()->toString(),
        type: 'deposit',
        amountCents: 12300,
        balanceAfterCents: 12300,
        currency: 'BRL',
        relatedUserEmail: null,
        relatedTransactionId: null,
        metadata: [],
        createdAt: now()->toIso8601String()
    );
    
    $array = $dto->toArray();
    
    expect($array['amount'])->toBe('123.00');
    expect($array['amount_cents'])->toBe(12300);
    expect($array['balance_after'])->toBe('123.00');
    expect($array['balance_after_cents'])->toBe(12300);
});

test('WalletDTO toArray includes created_at', function () {
    $dto = WalletDTO::fromPrimitives(
        id: Str::orderedUuid()->toString(),
        userId: Str::orderedUuid()->toString(),
        balanceCents: 5000,
        currency: 'BRL',
        createdAt: '2024-06-15T12:00:00Z'
    );
    
    $array = $dto->toArray();
    
    expect($array)->toHaveKey('created_at');
    expect($array['created_at'])->toContain('2024-06-15');
});
