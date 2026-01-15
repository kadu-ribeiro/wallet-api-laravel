<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\ApplicationBuilder;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->useAppPath(dirname(__DIR__).DIRECTORY_SEPARATOR.'src');

$builder = new ApplicationBuilder($app);
$builder
    ->withKernels()
    ->withEvents()
    ->withCommands()
    ->withProviders();

return $builder
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {})
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
