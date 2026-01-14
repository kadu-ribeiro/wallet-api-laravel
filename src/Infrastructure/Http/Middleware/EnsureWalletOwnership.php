<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Domain\User\Services\AuthContextInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureWalletOwnership
{
    public function __construct(
        private AuthContextInterface $authContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $walletId = $request->route('walletId');

        if (! $walletId) {
            return $next($request);
        }

        if ($walletId !== $this->authContext->getWalletId()->value) {
            return response()->json(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
