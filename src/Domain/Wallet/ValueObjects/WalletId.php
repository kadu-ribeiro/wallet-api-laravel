<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

use App\Domain\Shared\ValueObjects\UuidIdentifier;

final readonly class WalletId extends UuidIdentifier {}
