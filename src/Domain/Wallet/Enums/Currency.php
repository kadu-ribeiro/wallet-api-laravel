<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Enums;

enum Currency: string
{
    case BRL = 'BRL';
    case USD = 'USD';
    case EUR = 'EUR';
}
