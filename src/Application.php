<?php

declare(strict_types=1);

namespace App;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\AuthContextInterface;
use App\Domain\User\Services\AuthenticationServiceInterface;
use App\Domain\Wallet\Queries\TransactionQueryInterface;
use App\Domain\Wallet\Queries\WalletQueryInterface;
use App\Domain\Wallet\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Infrastructure\Auth\LaravelAuthenticatedUserProvider;
use App\Infrastructure\Auth\SanctumAuthenticationService;
use App\Infrastructure\Http\Controllers\TransferController;
use App\Infrastructure\Http\Controllers\UserController;
use App\Infrastructure\Http\Controllers\WalletController;
use App\Infrastructure\Http\Exceptions\ExceptionHandler;
use App\Infrastructure\Http\Middleware\ValidateIdempotencyKey;
use App\Infrastructure\Persistence\Queries\TransactionQuery;
use App\Infrastructure\Persistence\Queries\WalletQuery;
use App\Infrastructure\Persistence\Repositories\TransactionRepository;
use App\Infrastructure\Persistence\Repositories\UserRepository;
use App\Infrastructure\Persistence\Repositories\WalletRepository;
use App\Infrastructure\Projectors\TransactionProjector;
use App\Infrastructure\Projectors\UserProjector;
use App\Infrastructure\Projectors\WalletProjector;
use App\Infrastructure\Reactors\TransferNotificationReactor;
use App\Infrastructure\Reactors\WebhookReactor;
use App\Infrastructure\Reactors\WelcomeEmailReactor;
use Illuminate\Contracts\Debug\ExceptionHandler as LaravelExceptionHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\EventSourcing\Projectionist;

class Application extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);

        $this->app->bind(TransactionQueryInterface::class, TransactionQuery::class);
        $this->app->bind(WalletQueryInterface::class, WalletQuery::class);
        $this->app->bind(AuthenticationServiceInterface::class, SanctumAuthenticationService::class);
        $this->app->bind(
            AuthContextInterface::class,
            LaravelAuthenticatedUserProvider::class
        );
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerEventHandlers();
        $this->registerExceptionHandlers();
    }

    private function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(function (): void {
                Route::prefix('auth')->group(function (): void {
                    Route::post('register', [UserController::class, 'register']);
                    Route::post('login', [UserController::class, 'login']);
                });

                Route::middleware('auth:sanctum')->group(function (): void {
                    Route::get('user', [UserController::class, 'show']);
                    Route::post('auth/logout', [UserController::class, 'logout']);

                    Route::prefix('wallet')->group(function (): void {
                        Route::get('/', [WalletController::class, 'show']);
                        Route::get('balance', [WalletController::class, 'show']);
                        Route::get('transactions', [WalletController::class, 'transactions']);

                        Route::post('deposit', [WalletController::class, 'deposit'])
                            ->middleware(ValidateIdempotencyKey::class);
                        Route::post('withdraw', [WalletController::class, 'withdraw'])
                            ->middleware(ValidateIdempotencyKey::class);
                    });

                    Route::post('transfers', [TransferController::class, 'store'])
                        ->middleware(ValidateIdempotencyKey::class);
                });

                Route::prefix('demo')->group(function (): void {
                    Route::get('users', [UserController::class, 'index']);
                    Route::get('users/{userId}', [UserController::class, 'showById']);
                    Route::get('wallets', [WalletController::class, 'index']);
                    Route::get('wallets/{walletId}', [WalletController::class, 'showById']);
                    Route::get('wallets/{walletId}/transactions', [WalletController::class, 'transactionsById']);
                });
            });
    }

    private function registerEventHandlers(): void
    {
        $this->app->make(Projectionist::class)
            ->addProjectors([
                UserProjector::class,
                WalletProjector::class,
                TransactionProjector::class,
            ])
            ->addReactors([
                WelcomeEmailReactor::class,
                TransferNotificationReactor::class,
                WebhookReactor::class,
            ]);
    }

    private function registerExceptionHandlers(): void
    {
        $this->app->booted(function (): void {
            $handler = $this->app->make(LaravelExceptionHandler::class);
            new ExceptionHandler()->register($handler);
        });
    }
}
