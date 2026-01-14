<?php

declare(strict_types=1);

use App\Domain\User\Exceptions\InvalidPasswordException;
use App\Domain\User\ValueObjects\Password;

test('password VO accepts valid password', function (): void {
    $password = Password::from('senha12345');

    expect($password->value)->toBe('senha12345');
});

test('password VO throws on empty string', function (): void {
    Password::from('');
})->throws(InvalidPasswordException::class, 'Password cannot be empty');

test('password VO throws on too short', function (): void {
    Password::from('1234567');
})->throws(InvalidPasswordException::class, 'Password must be at least 8 characters');

test('password VO accepts exactly 8 characters', function (): void {
    $password = Password::from('12345678');

    expect($password->value)->toBe('12345678');
});
