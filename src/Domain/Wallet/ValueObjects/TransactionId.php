<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

use App\Domain\Shared\ValueObjects\IntegerIdentifier;

final readonly class TransactionId extends IntegerIdentifier {}
