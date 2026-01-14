<?php

declare(strict_types=1);

namespace Tests;

use App\Application\Contracts\Wallet\TransactionFinderInterface;
use App\Application\Contracts\Wallet\WalletFinderInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\AuthenticationServiceInterface;
use App\Domain\Wallet\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Infrastructure\Auth\SanctumAuthenticationService;
use App\Infrastructure\Persistence\Finders\EloquentTransactionFinder;
use App\Infrastructure\Persistence\Finders\EloquentWalletFinder;
use App\Infrastructure\Persistence\Repositories\TransactionRepository;
use App\Infrastructure\Persistence\Repositories\UserRepository;
use App\Infrastructure\Persistence\Repositories\WalletRepository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $app->bind(UserRepositoryInterface::class, UserRepository::class);
        $app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $app->bind(AuthenticationServiceInterface::class, SanctumAuthenticationService::class);
        $app->bind(WalletFinderInterface::class, EloquentWalletFinder::class);
        $app->bind(TransactionFinderInterface::class, EloquentTransactionFinder::class);

        return $app;
    }
}
