<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use DomainException;

final class RecipientNotFoundException extends DomainException
{
    public static function withEmail(string $email): self
    {
        return new self("Recipient with email '{$email}' not found");
    }

    public static function walletNotFound(string $email): self
    {
        return new self("Wallet not found for recipient '{$email}'");
    }
}
