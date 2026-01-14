<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use Exception;
use Throwable;

final class InternalServerErrorException extends Exception
{
    public static function fromException(Throwable $e): self
    {
        return new self(
            message: 'An unexpected error occurred. Please try again later.',
            previous: $e
        );
    }

    public function getContext(): array
    {
        return [
            'previous_message' => $this->getPrevious()?->getMessage(),
            'previous_trace' => $this->getPrevious()?->getTraceAsString(),
        ];
    }
}
