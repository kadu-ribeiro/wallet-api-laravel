<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdrawal';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';

    public function isDebit(): bool
    {
        return match ($this) {
            self::WITHDRAW, self::TRANSFER_OUT => true,
            default => false,
        };
    }

    public function isCredit(): bool
    {
        return ! $this->isDebit();
    }

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Depósito',
            self::WITHDRAW => 'Saque',
            self::TRANSFER_IN => 'Transferência Recebida',
            self::TRANSFER_OUT => 'Transferência Enviada',
        };
    }
}
