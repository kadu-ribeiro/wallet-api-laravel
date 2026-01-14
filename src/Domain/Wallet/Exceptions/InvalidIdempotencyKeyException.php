<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use DomainException;

final class InvalidIdempotencyKeyException extends DomainException {}
