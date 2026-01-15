<?php

declare(strict_types=1);

use App\Domain\Shared\Exceptions\InvalidIdentifierException;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\ValueObjects\WalletId;
use Illuminate\Support\Str;

test('userId VO accepts valid UUID', function (): void {
    $uuid = Str::uuid()->toString();
    $userId = new UserId($uuid);

    expect($userId->value)->toBe($uuid);
});

test('userId VO throws on invalid UUID', function (): void {
    new UserId('not-a-uuid');
})->throws(InvalidIdentifierException::class);

test('walletId VO accepts valid UUID', function (): void {
    $uuid = Str::uuid()->toString();
    $walletId = new WalletId($uuid);

    expect($walletId->value)->toBe($uuid);
});

test('userId equality works', function (): void {
    $uuid = Str::uuid()->toString();
    $userId1 = new UserId($uuid);
    $userId2 = new UserId($uuid);
    $userId3 = new UserId(Str::uuid()->toString());

    expect($userId1->equals($userId2))->toBeTrue();
    expect($userId1->equals($userId3))->toBeFalse();
});

test('userId toString works', function (): void {
    $uuid = Str::uuid()->toString();
    $userId = new UserId($uuid);

    expect((string) $userId)->toBe($uuid);
});
