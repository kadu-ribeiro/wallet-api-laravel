<?php

declare(strict_types=1);

use App\Domain\User\Exceptions\InvalidUserNameException;
use App\Domain\User\ValueObjects\UserName;

test('userName VO accepts valid name', function (): void {
    $name = UserName::from('João Silva');

    expect($name->value)->toBe('João Silva');
});

test('userName VO trims whitespace', function (): void {
    $name = UserName::from('  José Santos  ');

    expect($name->value)->toBe('José Santos');
});

test('userName VO throws on empty string', function (): void {
    UserName::from('');
})->throws(InvalidUserNameException::class, 'User name cannot be empty');

test('userName VO throws on too short', function (): void {
    UserName::from('J');
})->throws(InvalidUserNameException::class, 'User name must be at least 2 characters');

test('userName VO throws on too long', function (): void {
    UserName::from(str_repeat('a', 256));
})->throws(InvalidUserNameException::class, 'User name cannot exceed 255 characters');

test('userName VO equality works', function (): void {
    $name1 = UserName::from('João Silva');
    $name2 = UserName::from('João Silva');
    $name3 = UserName::from('Maria Santos');

    expect($name1->equals($name2))->toBeTrue();
    expect($name1->equals($name3))->toBeFalse();
});
