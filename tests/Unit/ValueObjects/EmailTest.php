<?php

declare(strict_types=1);

use App\Domain\User\Exceptions\InvalidEmailException;
use App\Domain\User\ValueObjects\Email;

test('email VO accepts valid email', function (): void {
    $email = Email::from('test@example.com');

    expect($email->value)->toBe('test@example.com');
});

test('email VO trims and lowercases', function (): void {
    $email = Email::from('  TeSt@Example.COM  ');

    expect($email->value)->toBe('test@example.com');
});

test('email VO throws on empty string', function (): void {
    Email::from('');
})->throws(InvalidEmailException::class, 'Email cannot be empty');

test('email VO throws on invalid format', function (): void {
    Email::from('not-an-email');
})->throws(InvalidEmailException::class, 'Invalid email format');

test('email VO equality works', function (): void {
    $email1 = Email::from('test@example.com');
    $email2 = Email::from('test@example.com');
    $email3 = Email::from('other@example.com');

    expect($email1->equals($email2))->toBeTrue();
    expect($email1->equals($email3))->toBeFalse();
});
