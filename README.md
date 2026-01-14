# Wallet API - Digital Wallet Management System

A RESTful API for managing a digital wallet with user registration, balance management, deposits, withdrawals, and transfers between accounts. Built with Domain-Driven Design (DDD), Event Sourcing, and CQRS patterns.

## Technical Challenge

**Challenge:** Build a Digital Wallet API in PHP

**Objective:**
Develop a RESTful API to manage a digital wallet, allowing operations for user registration, balance inquiry, deposits, withdrawals, and transfers between accounts.

**Functional Requirements:**
1. User Registration - Create users with unique email and zero initial balance
2. Balance Query - Query authenticated user's balance
3. Deposit - Add money to wallet with positive amount validation
4. Withdrawal - Remove money from wallet with balance validation
5. Transfer - Send money to another user by email
6. Transaction History - List all wallet transactions

**Bonus Features:**
- Automated test coverage
- Docker for easy setup
- Daily withdrawal and deposit limits
- Webhooks for transfer notifications

## Project Structure

This project uses a custom folder structure that better reflects the implemented architecture, deviating from Laravel's default structure:

```
src/
├── Domain/              # Business rules (framework-agnostic)
│   ├── User/
│   │   ├── Aggregates/  # UserAggregate
│   │   ├── Events/      # UserCreated
│   │   ├── ValueObjects/# Email, UserId, UserName, Password
│   │   ├── Exceptions/  # InvalidEmailException, etc.
│   │   └── Repositories/# Repository interfaces
│   ├── Wallet/
│   │   ├── Aggregates/  # WalletAggregate
│   │   ├── Events/      # MoneyDeposited, MoneyWithdrawn, etc.
│   │   ├── ValueObjects/# Money, WalletId
│   │   ├── Enums/       # TransactionType, Currency
│   │   └── Exceptions/  # InsufficientBalanceException, etc.
│   └── Shared/
│       └── ValueObjects/# UuidIdentifier, IntegerIdentifier
├── Application/         # Use cases and orchestration
│   ├── DTOs/            # Data Transfer Objects
│   └── UseCases/        # Business workflows
└── Infrastructure/      # Technical implementation details
    ├── Persistence/     # Eloquent models, repositories
    ├── Http/            # Controllers, requests, middleware
    ├── Projectors/      # Read model projections
    ├── Reactors/        # Side effects (emails, webhooks)
    └── Mail/            # Email templates
```

**Important:** The `src/Application.php` file is our application's entry point and service provider. It's where all routes are defined and serves as the starting point for code navigation. Check this file first to understand how the API is structured.

## Architecture Overview

### Domain-Driven Design (DDD)

The domain layer contains pure business logic with no framework dependencies. All business rules are expressed through:

- **Value Objects** - Type-safe wrappers with validation (Email, Money, UserId)
- **Aggregates** - Consistency boundaries that emit domain events
- **Events** - Immutable facts that represent state changes
- **Exceptions** - Domain-specific errors

### Event Sourcing

Instead of storing just the current state, we store the complete history of events. This gives us:

- Complete audit trail of all operations
- Ability to rebuild state from events
- Time travel for debugging
- Event replay capabilities

All events are stored in the `stored_events` table, which serves as the source of truth.

### CQRS (Command Query Responsibility Segregation)

We separate write operations (commands) from read operations (queries):

- **Write side**: Commands go through aggregates and emit events
- **Read side**: Projectors build optimized read models from events

This separation allows us to optimize each side independently.

## Requirements & Setup

**Requirements:**
- Docker & Docker Compose
- Git

**Quick Start:**

```bash
# Clone the repository
git clone <repository-url>
cd wallet-api-laravel

# Start Docker containers
docker-compose up -d

# Run migrations
docker-compose exec laravel php artisan migrate

# You're ready to go!
# API: http://localhost:8080
# Email viewer (Mailpit): http://localhost:8025
```

## Testing the API

### Option 1: Postman (Recommended)

Import the Postman collection to test the API with pre-configured requests:

1. Open Postman
2. Click "Import" → "Upload Files"
3. Select `postman_collection.json` from the project root
4. The collection includes:
   - All endpoints with example requests
   - Auto-generated Idempotency-Key for each request
   - Automatic token and wallet_id management
   - Test assertions for responses

**Typical Flow:**
1. Register → Creates user + wallet, returns token
2. Login → Returns token (if already registered)
3. Deposit → Adds money to wallet
4. Transfer → Sends money to another user by email
5. Withdraw → Removes money from wallet
6. Get Transactions → View transaction history

### Option 2: cURL

See examples in the "Functional Requirements & Implementation" section below.

## Functional Requirements & Implementation

### 1. User Registration

**Requirement:**
Endpoint to create a new user (name, email, password). Email must be unique. Initial balance must be zero.

**Implementation:**
- **Use Case:** `src/Application/UseCases/User/RegisterUserUseCase.php`
- **Aggregate:** `src/Domain/User/Aggregates/UserAggregate.php`
- **Controller:** `src/Infrastructure/Http/Controllers/UserController.php` → `register()`
- **Route:** `POST /api/auth/register`
- **Event Emitted:** `UserCreated` (triggers wallet creation and welcome email)

**How it works:**
When a user registers, the `UserAggregate` emits a `UserCreated` event. This event is listened to by the `UserProjector` (creates user record) and `WalletProjector` (creates wallet with zero balance). The `WelcomeEmailReactor` also sends a welcome email asynchronously.

**Example:**
```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "senha123456",
    "password_confirmation": "senha123456"
  }'
```

**Response:**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": "a0d58f6c-a85e-4df4-a638-f30cec3b93f1",
    "name": "John Doe",
    "email": "john@example.com"
  },
  "wallet_id": "a0d58f6c-f333-4448-8ac0-1ee1fd885232",
  "token": "3|wK1DvSA2PqJJMgBrEn5qFIDCUAdk..."
}
```

### 2. Balance Query

**Requirement:**
Endpoint to query the balance of the authenticated user.

**Implementation:**
- **Use Case:** `src/Application/UseCases/Wallet/GetBalanceUseCase.php`
- **Query:** `src/Infrastructure/Persistence/Queries/WalletQuery.php`
- **Controller:** `src/Infrastructure/Http/Controllers/WalletController.php` → `balance()`
- **Route:** `GET /api/wallets/{walletId}/balance`

**How it works:**
The balance is read from the optimized read model (wallets table) which is kept up-to-date by the `WalletProjector` listening to wallet events.

**Example:**
```bash
curl -X GET http://localhost:8080/api/wallets/{walletId}/balance \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "balance_cents": 100000,
  "balance": "1000.00",
  "currency": "BRL"
}
```

### 3. Deposit

**Requirement:**
Endpoint to deposit money into the authenticated user's wallet. Amount must be positive.

**Implementation:**
- **Use Case:** `src/Application/UseCases/Wallet/DepositMoneyUseCase.php`
- **Aggregate:** `src/Domain/Wallet/Aggregates/WalletAggregate.php`
- **Controller:** `src/Infrastructure/Http/Controllers/WalletController.php` → `deposit()`
- **Route:** `POST /api/wallets/{walletId}/deposit`
- **Event Emitted:** `MoneyDeposited`
- **Idempotency:** Required via `Idempotency-Key` header

**Why Idempotency Key?**
The `Idempotency-Key` header prevents duplicate operations in case of network issues or user retry. If you send the same idempotency key twice, the second request will be rejected with a 409 Conflict error. This ensures that even if a user accidentally clicks "deposit" twice, the money is only added once. The key must be a valid UUID and is stored with each transaction to detect duplicates.

**Example:**
```bash
curl -X POST http://localhost:8080/api/wallets/{walletId}/deposit \
  -H "Authorization: Bearer {token}" \
  -H "Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": "100.00"
  }'
```

**Response:**
```json
{
  "message": "Deposit successful",
  "wallet": {
    "id": "a0d58f6c-f333-4448-8ac0-1ee1fd885232",
    "balance_cents": 110000,
    "balance": "1100.00",
    "currency": "BRL"
  }
}
```

### 4. Withdrawal

**Requirement:**
Endpoint to withdraw money from the authenticated user's wallet. Validate sufficient balance.

**Implementation:**
- **Use Case:** `src/Application/UseCases/Wallet/WithdrawMoneyUseCase.php`
- **Aggregate:** `src/Domain/Wallet/Aggregates/WalletAggregate.php`
- **Controller:** `src/Infrastructure/Http/Controllers/WalletController.php` → `withdraw()`
- **Route:** `POST /api/wallets/{walletId}/withdraw`
- **Event Emitted:** `MoneyWithdrawn`
- **Validations:** 
  - Balance check: `ensureSufficientBalance()`
  - Daily limit: `ensureDailyWithdrawalLimitNotExceeded()`
- **Idempotency:** Required via `Idempotency-Key` header

**Example:**
```bash
curl -X POST http://localhost:8080/api/wallets/{walletId}/withdraw \
  -H "Authorization: Bearer {token}" \
  -H "Idempotency-Key: 650e8400-e29b-41d4-a716-446655440001" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": "50.00"
  }'
```

**Response:**
```json
{
  "message": "Withdrawal successful",
  "wallet": {
    "id": "a0d58f6c-f333-4448-8ac0-1ee1fd885232",
    "balance_cents": 105000,
    "balance": "1050.00",
    "currency": "BRL"
  }
}
```

### 5. Transfer

**Requirement:**
Endpoint to transfer money to another user by email. Validate sufficient balance and recipient existence.

**Implementation:**
- **Use Case:** `src/Application/UseCases/Wallet/TransferMoneyUseCase.php`
- **Aggregates:** Both sender and recipient `WalletAggregate`
- **Controller:** `src/Infrastructure/Http/Controllers/TransferController.php` → `store()`
- **Route:** `POST /api/transfers`
- **Events Emitted:** 
  - `MoneyTransferredOut` (sender)
  - `MoneyTransferredIn` (recipient)
- **Validations:**
  - Balance check
  - Daily transfer limit
  - Recipient exists
  - No self-transfer
- **Idempotency:** Required via `Idempotency-Key` header

**How it works:**
The transfer operation updates both wallets in a single database transaction. The sender's aggregate emits `MoneyTransferredOut`, and the recipient's aggregate emits `MoneyTransferredIn`. The `TransferNotificationReactor` sends an email to the recipient, and the `WebhookProjector` triggers a webhook notification (if configured).

**Example:**
```bash
curl -X POST http://localhost:8080/api/transfers \
  -H "Authorization: Bearer {token}" \
  -H "Idempotency-Key: 750e8400-e29b-41d4-a716-446655440002" \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_email": "jane@example.com",
    "amount": "25.00",
    "metadata": {
      "description": "Payment for services"
    }
  }'
```

**Response:**
```json
{
  "message": "Transfer successful",
  "sender": {
    "wallet_id": "a0d58f6c-f333-4448-8ac0-1ee1fd885232",
    "balance_cents": 102500,
    "balance": "1025.00"
  },
  "recipient": {
    "wallet_id": "b1f69a7d-e444-5559-be23-3f524456bb43",
    "balance_cents": 2500,
    "balance": "25.00"
  },
  "amount_cents": 2500,
  "amount": "25.00"
}
```

### 6. Transaction History

**Requirement:**
Endpoint to list all transactions for the authenticated user's wallet.

**Implementation:**
- **Use Case:** `src/Application/UseCases/Wallet/GetTransactionHistoryUseCase.php`
- **Query:** `src/Infrastructure/Persistence/Queries/TransactionQuery.php`
- **Controller:** `src/Infrastructure/Http/Controllers/WalletController.php` → `transactions()`
- **Route:** `GET /api/wallets/{walletId}/transactions`
- **Projection:** Read model updated by `TransactionProjector`

**How it works:**
Every time a wallet event occurs (deposit, withdrawal, transfer), the `TransactionProjector` creates a record in the transactions table. This is our optimized read model for querying transaction history.

**Example:**
```bash
curl -X GET http://localhost:8080/api/wallets/{walletId}/transactions \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
[
  {
    "id": 5,
    "wallet_id": "a0d58f6c-f333-4448-8ac0-1ee1fd885232",
    "type": "transfer_out",
    "amount_cents": 2500,
    "amount": "25.00",
    "balance_after_cents": 102500,
    "balance_after": "1025.00",
    "currency": "BRL",
    "related_user_email": "jane@example.com",
    "related_transaction_id": 6,
    "metadata": {
      "description": "Payment for services"
    },
    "created_at": "2026-01-14T11:50:17Z"
  },
  {
    "id": 4,
    "wallet_id": "a0d58f6c-f333-4448-8ac0-1ee1fd885232",
    "type": "withdrawal",
    "amount_cents": 5000,
    "amount": "50.00",
    "balance_after_cents": 105000,
    "balance_after": "1050.00",
    "currency": "BRL",
    "related_user_email": null,
    "related_transaction_id": null,
    "metadata": {},
    "created_at": "2026-01-14T11:48:15Z"
  }
]
```

## Additional Features (Bonus)

### Automated Test Coverage

We have comprehensive test coverage using Pest PHP:

**Test Results:**
- 111 unit/integration/feature tests (196 assertions) - All passing
- 24 end-to-end tests covering complete user flows - All passing

**Running Tests:**
```bash
# All tests
docker-compose exec laravel php artisan test

# E2E tests
docker-compose exec laravel sh tests/e2e_tests.sh
```

**Test Structure:**
- `tests/Unit/` - Domain logic, Value Objects, Enums
- `tests/Integration/` - Aggregate behavior with event sourcing
- `tests/Feature/` - API endpoints and authorization
- `tests/E2E/` - Complete user flows via shell script

### Docker Setup

The project is fully containerized with Docker Compose for easy setup:

**Services:**
- Laravel with Octane (Swoole) for high performance
- Redis for queue and cache
- Mailpit for email testing (accessible at http://localhost:8025)

No need to install PHP, Composer, or any dependencies locally. Everything runs in containers.

### Daily Limits

Configurable daily limits for withdrawals and transfers to prevent abuse:

**Configuration:**
```php
// config/wallet.php
'daily_limits' => [
    'withdrawal' => 500000, // R$ 5,000.00
    'transfer' => 500000,   // R$ 5,000.00
],
```

**Environment Variables:**
```env
WALLET_DAILY_WITHDRAWAL_LIMIT=500000
WALLET_DAILY_TRANSFER_LIMIT=500000
```

The limits are enforced at the aggregate level and reset daily. The logic is in `src/Domain/Wallet/Aggregates/WalletAggregate.php`.

### Webhooks

Webhook notifications are sent when transfers are received:

**Configuration (.env):**
```env
WALLET_WEBHOOK_TRANSFER_URL=https://webhook.site/your-unique-id
```

**Implementation:**
- Projector: `src/Infrastructure/Projectors/WebhookProjector.php`
- Job: `src/Infrastructure/Jobs/SendWebhookJob.php`

**Payload Example:**
```json
{
  "event": "transfer.received",
  "data": {
    "transfer_id": "a0d58f6c-f333-4448-8ac0-1ee1fd885232",
    "sender_email": "john@example.com",
    "recipient_email": "jane@example.com",
    "amount_cents": 2500,
    "amount": "25.00",
    "currency": "BRL",
    "timestamp": "2026-01-14T11:50:17Z"
  }
}
```

Webhooks are sent asynchronously via queue to avoid blocking the API response.

## API Documentation

### Base URL
```
http://localhost:8080/api
```

### Authentication

All protected endpoints require Bearer token authentication via Laravel Sanctum.

**Header:**
```
Authorization: Bearer {token}
```

The token is obtained from the register or login endpoints and should be included in all subsequent requests.

### Idempotency

Write operations (deposit, withdraw, transfer) require an idempotency key to prevent duplicate operations.

**Header:**
```
Idempotency-Key: {uuid}
```

**Why is this important?**
In distributed systems and APIs, network issues can cause requests to be retried. Without idempotency keys, a retry could result in duplicate operations (e.g., money deposited twice). The idempotency key ensures that if you send the same request twice with the same key, only the first one is processed. The second request returns a 409 Conflict error.

**Implementation:**
The idempotency key is stored in the transactions table. The database enforces uniqueness via a unique constraint, preventing race conditions even in concurrent scenarios.

### Endpoints Summary

| Method | Endpoint | Description | Auth | Idempotency |
|--------|----------|-------------|------|-------------|
| POST | `/auth/register` | Register new user | No | No |
| POST | `/auth/login` | Login user | No | No |
| POST | `/auth/logout` | Logout user | Yes | No |
| GET | `/user` | Get authenticated user info | Yes | No |
| GET | `/users/{userId}/wallet` | Get wallet by user ID | Yes | No |
| GET | `/wallets/{walletId}` | Get wallet details | Yes | No |
| GET | `/wallets/{walletId}/balance` | Get wallet balance | Yes | No |
| GET | `/wallets/{walletId}/transactions` | Get transaction history | Yes | No |
| POST | `/wallets/{walletId}/deposit` | Deposit money | Yes | Yes |
| POST | `/wallets/{walletId}/withdraw` | Withdraw money | Yes | Yes |
| POST | `/transfers` | Transfer money | Yes | Yes |

### Error Responses

All errors follow a standardized JSON format:

```json
{
  "error": "Error message description"
}
```

**HTTP Status Codes:**
- `200` - Success
- `201` - Created (user registration)
- `400` - Bad Request (missing idempotency key)
- `401` - Unauthorized (invalid credentials or missing token)
- `404` - Not Found (user/wallet not found)
- `409` - Conflict (duplicate email, duplicate idempotency key)
- `422` - Unprocessable Entity (validation errors, insufficient balance)
- `500` - Internal Server Error

## Database Schema

### Core Tables

**users**
- Stores user authentication data
- Links to wallet via one-to-one relationship

**wallets**
- Current wallet state (snapshot)
- Updated by `WalletProjector` listening to events

**transactions**
- Read model for transaction history
- Updated by `TransactionProjector`
- Includes metadata field for additional transaction information

**stored_events**
- Event sourcing event store
- Source of truth for all state changes
- Immutable append-only log
- Can rebuild entire system state from these events

**snapshots**
- Periodic aggregate snapshots for performance
- Speeds up aggregate reconstruction
- Reduces need to replay all events

## Technology Stack

- **Framework:** Laravel 11
- **PHP:** 8.3+
- **Event Sourcing:** spatie/laravel-event-sourcing
- **Server:** Laravel Octane (Swoole)
- **Authentication:** Laravel Sanctum
- **Database:** SQLite
- **Queue:** Redis
- **Testing:** Pest PHP
- **Money Handling:** brick/money

## Development

### Running the Application

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f laravel

# Stop services
docker-compose down
```

### Useful Commands

```bash
# Access Laravel container
docker-compose exec laravel bash

# Clear cache
docker-compose exec laravel php artisan cache:clear

# Reload Octane workers (after code changes)
docker-compose exec laravel php artisan octane:reload

# Start queue worker (for emails and webhooks)
docker-compose exec laravel php artisan queue:work

# List all routes
docker-compose exec laravel php artisan route:list

# List events and projectors
docker-compose exec laravel php artisan event-sourcing:list
```

### Running Tests

```bash
# All tests (unit, integration, feature)
docker-compose exec laravel php artisan test

# Specific test suite
docker-compose exec laravel php artisan test --testsuite=Unit
docker-compose exec laravel php artisan test --testsuite=Feature

# E2E tests
docker-compose exec laravel sh tests/e2e_tests.sh
```

## Configuration

### Environment Variables

Key configuration options in `.env`:

```env
# Database
DB_CONNECTION=sqlite

# Queue
QUEUE_CONNECTION=redis

# Mail (Mailpit for development)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# Wallet Limits
WALLET_DAILY_WITHDRAWAL_LIMIT=500000
WALLET_DAILY_TRANSFER_LIMIT=500000

# Features
WALLET_SEND_WELCOME_EMAIL=true
WALLET_SEND_TRANSFER_EMAIL=true
WALLET_WEBHOOK_TRANSFER_URL=https://webhook.site/your-id
```

## Key Design Decisions

### Why Event Sourcing?

Instead of just storing the current state (balance), we store every event that changed that state (deposits, withdrawals, transfers). This gives us:

- Complete audit trail - see exactly what happened and when
- Debugging capabilities - replay events to reproduce issues
- Business insights - analyze patterns in transaction history
- Regulatory compliance - immutable record of all operations

### Why Idempotency Keys?

In real-world scenarios, network issues and user behavior can cause duplicate requests. For example:
- User clicks "deposit" twice quickly
- Network timeout causes automatic retry
- Browser refresh after submitting form

Without idempotency keys, these scenarios could result in duplicate operations. By requiring a unique UUID for each operation and storing it with the transaction, we ensure that the same operation cannot be performed twice.

### Why CQRS?

Separating commands (writes) from queries (reads) allows us to:
- Optimize writes for consistency and business rules
- Optimize reads for performance (no complex joins)
- Scale reads and writes independently
- Use different data models for each side

### Why Custom Folder Structure?

Laravel's default structure mixes infrastructure concerns with business logic. By using a custom structure that separates Domain, Application, and Infrastructure layers, we achieve:

- Clear separation of concerns
- Framework-independent business logic
- Easier testing of domain logic
- Better code organization for larger teams

The `src/Application.php` service provider is our entry point and makes navigation easier by having all routes in one place.

## Bibliographical References

This project implements concepts from the following literature:

- **[Domain-Driven Design](https://www.domainlanguage.com/ddd/reference/) (Eric Evans)**
  - *Concepts:* Aggregates, Value Objects, Domain Isolation.
  - *Implementation:* `src/Domain/` (e.g. `Aggregates/`, `ValueObjects/` independent of framework).

- **[Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html) (Robert C. Martin)**
  - *Concepts:* Layer separation, Dependency Rule (Inner layers know nothing of outer layers).
  - *Implementation:* Strict separation between `src/Application/` (Use Cases) and `src/Infrastructure/`.

- **[Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html) & [CQRS](https://martinfowler.com/bliki/CQRS.html) (Martin Fowler)**
  - *Concepts:* State changes as immutable events, separating Read (Query) and Write (Command) models.
  - *Implementation:* `src/Infrastructure/Projectors/` (Read Models) vs `src/Domain/*/Aggregates/` (Write Models).

- **[Laravel Event Sourcing](https://spatie.be/docs/laravel-event-sourcing) (Spatie)**
  - *Resources:* [Introductory Video](https://www.youtube.com/watch?v=1VWqmfMEsF8) explaining the package philosophy.
  - *Implementation:* The engine under the hood handling our Event Store and Projectors.


## How to Navigate the Code

1. **Start with:** `src/Application.php` - All routes are defined here
2. **Follow a request:** Route → Controller → Use Case → Aggregate → Event
3. **Understand events:** Check `src/Domain/*/Events/` for all possible events
4. **See side effects:** Check `src/Infrastructure/Reactors/` for what happens after events
5. **Understand read models:** Check `src/Infrastructure/Projectors/` for how data is projected

**Example flow for a deposit:**
1. `POST /api/wallets/{id}/deposit` (defined in `Application.php`)
2. `WalletController::deposit()` validates auth and extracts data
3. `DepositMoneyUseCase` orchestrates the operation
4. `WalletAggregate` applies business rules and emits `MoneyDeposited` event
5. `TransactionProjector` creates transaction record
6. `WalletProjector` updates wallet balance
7. Response sent back to user

All of this happens in a single database transaction for consistency.
