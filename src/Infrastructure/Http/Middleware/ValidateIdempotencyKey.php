<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class ValidateIdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        if (empty($idempotencyKey)) {
            return response()->json([
                'error' => 'Idempotency-Key header is required for this operation',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (! Str::isUuid($idempotencyKey)) {
            return response()->json([
                'error' => 'Idempotency-Key must be a valid UUID',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $next($request);
    }
}
