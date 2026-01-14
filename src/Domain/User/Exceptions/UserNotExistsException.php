<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use Exception;

class UserNotExistsException extends Exception
{
    public static function withEmail(string $email): self
    {
        return new self("User with email {$email} not exists");
    }
}
