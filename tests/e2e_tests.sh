#!/bin/sh
# Wallet API - E2E Test Suite (Enhanced with JSON Headers)
# Execute: docker-compose exec laravel sh tests/e2e_tests.sh

set -e

echo "==================================================================="
echo "         WALLET API - ENHANCED E2E TEST SUITE"
echo "==================================================================="

echo "\n>>> Checking dependencies..."
if ! command -v jq > /dev/null 2>&1; then
    echo "Installing jq..."
    apk add --no-cache jq
fi
echo "✓ Dependencies OK"

API_URL="http://127.0.0.1:8080/api"

echo "\n>>> SETUP: Database Reset"
php artisan migrate:fresh --force
echo "✓ Database reset complete\n"

# Test counters
TESTS_TOTAL=0
TESTS_PASSED=0
TESTS_FAILED=0

run_curl() {
    # Helper to run curl with standard headers for JSON API
    curl -s -H "Accept: application/json" -H "Content-Type: application/json" "$@"
}

assert_http_code() {
    local expected=$1
    local actual=$2
    local test_name=$3
    TESTS_TOTAL=$((TESTS_TOTAL + 1))
    
    if [ "$expected" = "$actual" ]; then
        echo "  ✓ HTTP $actual (Expected: $expected)"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo "  ✗ HTTP $actual (Expected: $expected) - FAILED"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
}

assert_json_contains() {
    local json=$1
    local key=$2
    TESTS_TOTAL=$((TESTS_TOTAL + 1))
    
    if echo "$json" | jq -e ".$key" > /dev/null 2>&1; then
        echo "  ✓ Response contains '$key'"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo "  ✗ Response missing '$key' - FAILED"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
}

# --- TEST 1: Register User (Alice) ---
echo "==================================================================="
echo ">>> TEST 1: Register User (Alice)"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/auth/register" \
  -d '{"name":"Alice","email":"alice@test.com","password":"password123","password_confirmation":"password123"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
assert_http_code "201" "$HTTP_CODE" "Register Alice"
assert_json_contains "$BODY" "wallet_id"
echo "$BODY" | jq -c '{user: .user.email, wallet_id: .wallet_id}'

# --- TEST 2: Register User (Bob) ---
echo "\n>>> TEST 2: Register User (Bob)"
run_curl -X POST "$API_URL/auth/register" \
  -d '{"name":"Bob","email":"bob@test.com","password":"password123","password_confirmation":"password123"}' > /dev/null
echo "✓ Bob registered"

# --- TEST 3: Duplicate Registration ---
echo "\n==================================================================="
echo ">>> TEST 3: Duplicate Registration (Expect 409/422)"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/auth/register" \
  -d '{"name":"Alice Duplicate","email":"alice@test.com","password":"password123","password_confirmation":"password123"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
if [ "$HTTP_CODE" = "409" ] || [ "$HTTP_CODE" = "422" ]; then
    echo "  ✓ HTTP $HTTP_CODE (Duplicate prevented)"
    TESTS_PASSED=$((TESTS_PASSED + 1))
else
    echo "  ✗ HTTP $HTTP_CODE - FAILED"
    TESTS_FAILED=$((TESTS_FAILED + 1))
fi
TESTS_TOTAL=$((TESTS_TOTAL + 1))

# --- TEST 4: Login (Alice) ---
echo "\n==================================================================="
echo ">>> TEST 4: Login (Alice)"
echo "==================================================================="
LOGIN=$(run_curl -X POST "$API_URL/auth/login" \
  -d '{"email":"alice@test.com","password":"password123"}')
TOKEN=$(echo "$LOGIN" | jq -r '.token')
WALLET_ID=$(echo "$LOGIN" | jq -r '.wallet_id')
USER_ID=$(echo "$LOGIN" | jq -r '.user.id')
ALICE_TOKEN=$TOKEN
ALICE_WALLET=$WALLET_ID
echo "Token: ${TOKEN:0:30}..."

# --- TEST 4.1: Get User Info (New Route) ---
echo "\n==================================================================="
echo ">>> TEST 4.1: GET /api/user (User Info)"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X GET "$API_URL/user" \
  -H "Authorization: Bearer $TOKEN")
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
assert_http_code "200" "$HTTP_CODE" "Get user info"
assert_json_contains "$BODY" "data"
echo "$BODY" | jq -c '.data | {id, name, email, wallet_id}'

# --- TEST 4.2: Get Wallet ---
echo "\n==================================================================="
echo ">>> TEST 4.2: GET /api/wallet"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X GET "$API_URL/wallet" \
  -H "Authorization: Bearer $TOKEN")
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
assert_http_code "200" "$HTTP_CODE" "Get wallet"
echo "$BODY" | jq -c '.data | {id, user_id, balance, currency}'

# --- TEST 5: Invalid Login ---
echo "\n==================================================================="
echo ">>> TEST 5: Invalid Login (Expect 401)"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/auth/login" \
  -d '{"email":"alice@test.com","password":"wrongpassword"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "401" "$HTTP_CODE" "Invalid password"

# --- TEST 6: Deposit 1000 ---
echo "\n==================================================================="
echo ">>> TEST 6: Deposit R\$1000"
echo "==================================================================="
UUID_DEP1=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_DEP1" \
  -d '{"amount":"1000.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
assert_http_code "200" "$HTTP_CODE" "Deposit"
echo "$BODY" | jq -c '{balance_cents, balance}'

# --- TEST 7: Negative Amount ---
echo "\n==================================================================="
echo ">>> TEST 7: Negative Amount (Expect 422)"
echo "==================================================================="
UUID_NEG=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_NEG" \
  -d '{"amount":"-100.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "422" "$HTTP_CODE" "Negative amount"

# --- TEST 8: Withdraw 200 ---
echo "\n==================================================================="
echo ">>> TEST 8: Withdraw R\$200"
echo "==================================================================="
UUID_WDW=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/withdraw" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_WDW" \
  -d '{"amount":"200.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
assert_http_code "200" "$HTTP_CODE" "Withdraw"
echo "$BODY" | jq -c '{balance_cents, balance}'

# --- TEST 9: Insufficient Balance ---
echo "\n==================================================================="
echo ">>> TEST 9: Insufficient Balance (Expect 422)"
echo "==================================================================="
UUID_INS=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/withdraw" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_INS" \
  -d '{"amount":"99999.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "422" "$HTTP_CODE" "Insufficient balance"

# --- TEST 10: Transfer 300 to Bob ---
echo "\n==================================================================="
echo ">>> TEST 10: Transfer R\$300 to Bob"
echo "==================================================================="
UUID_TRF=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/transfers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_TRF" \
  -d '{"recipient_email":"bob@test.com","amount":"300.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
assert_http_code "200" "$HTTP_CODE" "Transfer"
echo "$BODY" | jq -c '{sender: {balance_cents}, recipient: {balance_cents}}'

# --- TEST 11: Self Transfer ---
echo "\n==================================================================="
echo ">>> TEST 11: Self Transfer (Expect 422)"
echo "==================================================================="
UUID_SELF=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/transfers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_SELF" \
  -d '{"recipient_email":"alice@test.com","amount":"10.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
# Request validation returns 422 (Unprocessable Entity)
assert_http_code "422" "$HTTP_CODE" "Self-transfer prevented"
echo "$BODY" | jq -c '{error}'

# --- TEST 12: Non-Existent Recipient ---
echo "\n==================================================================="
echo ">>> TEST 12: Non-Existent Recipient (Expect 404)"
echo "==================================================================="
UUID_404=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/transfers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_404" \
  -d '{"recipient_email":"nonexistent@test.com","amount":"10.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "404" "$HTTP_CODE" "Non-existent user"

# --- TEST 13: Idempotency Duplicate ---
echo "\n==================================================================="
echo ">>> TEST 13: Idempotency - Duplicate Deposit (Expect 409)"
echo "==================================================================="
UUID_DUP=$(cat /proc/sys/kernel/random/uuid)
# First request
run_curl -X POST "$API_URL/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_DUP" \
  -d '{"amount":"50.00"}' > /dev/null
# Duplicate request
RESPONSE2=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $UUID_DUP" \
  -d '{"amount":"50.00"}')
HTTP2=$(echo "$RESPONSE2" | tail -1)
assert_http_code "409" "$HTTP2" "Duplicate idempotency key"

# --- TEST 14: Missing Idempotency Key ---
echo "\n==================================================================="
echo ">>> TEST 14: Missing Idempotency-Key (Expect 400)"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"amount":"10.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "400" "$HTTP_CODE" "Missing idempotency key"

# --- TEST 15: Invalid Idempotency Key ---
echo "\n==================================================================="
echo ">>> TEST 15: Invalid Idempotency-Key (Expect 422)"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: invalid-key" \
  -d '{"amount":"10.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "422" "$HTTP_CODE" "Invalid UUID"

# --- TEST 16: Unauthorized Request ---
echo "\n==================================================================="
echo ">>> TEST 16: Unauthorized Request (Expect 401)"
echo "==================================================================="
# No token, Expect 401
RESPONSE=$(run_curl -w "\n%{http_code}" -X GET "$API_URL/wallet/balance")
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "401" "$HTTP_CODE" "Unauthorized"

# --- TEST 17: Transaction History ---
echo "\n==================================================================="
echo ">>> TEST 17: Transaction History"
echo "==================================================================="
RESPONSE=$(run_curl -w "\n%{http_code}" -X GET "$API_URL/wallet/transactions" \
  -H "Authorization: Bearer $TOKEN")
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
BODY=$(echo "$RESPONSE" | sed '$d')
assert_http_code "200" "$HTTP_CODE" "Get transactions"
echo "$BODY" | jq -c '.[] | {type, amount_cents}' | head -3

# ===================================================================
# TEST 20: Zero Balance Withdraw (Expect 422)
# ===================================================================
echo "\n==================================================================="
echo ">>> TEST 20: Zero Balance Withdraw (Expect 422)"
echo "==================================================================="
RESPONSE=$(run_curl -X POST "$API_URL/auth/register" -d '{"name":"Charlie","email":"charlie@test.com","password":"Test@123","password_confirmation":"Test@123"}')
CHARLIE_TOKEN=$(echo "$RESPONSE" | jq -r '.token')
CHARLIE_WALLET=$(echo "$RESPONSE" | jq -r '.wallet_id')
UUID_ZERO=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/wallet/withdraw" -H "Authorization: Bearer $CHARLIE_TOKEN" -H "Idempotency-Key: $UUID_ZERO" -d '{"amount":"10.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "422" "$HTTP_CODE" "Zero balance withdraw"

# ===================================================================
# TEST 21: Login After Register
# ===================================================================
echo "\n==================================================================="
echo ">>> TEST 21: Login After Register"
echo "==================================================================="
run_curl -X POST "$API_URL/auth/register" -d '{"name":"David","email":"david@test.com","password":"Test@123","password_confirmation":"Test@123"}' > /dev/null
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/auth/login" -d '{"email":"david@test.com","password":"Test@123"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "200" "$HTTP_CODE" "Login after register"

# ===================================================================
# TEST 22: Medium Amount Transfer (within daily limits)
# ===================================================================
echo "\n==================================================================="
echo ">>> TEST 22: Medium Amount Transfer"
echo "==================================================================="
# Deposit R$ 4000 to Alice (Alice already has ~R$550 from previous tests)
UUID_LARGE=$(cat /proc/sys/kernel/random/uuid)
run_curl -X POST "$API_URL/wallet/deposit" -H "Authorization: Bearer $ALICE_TOKEN" -H "Idempotency-Key: $UUID_LARGE" -d '{"amount":"4000.00"}' > /dev/null
# Transfer R$ 2000 to Bob (within R$ 5k daily limit)
UUID_TRF=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/transfers" -H "Authorization: Bearer $ALICE_TOKEN" -H "Idempotency-Key: $UUID_TRF" -d '{"recipient_email":"bob@test.com","amount":"2000.00"}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "200" "$HTTP_CODE" "Medium amount transfer"

# ===================================================================
# TEST 23: Metadata Persistence
# ===================================================================
echo "\n==================================================================="
echo ">>> TEST 23: Metadata Persistence"
echo "==================================================================="
# Transfer with metadata from Alice to Bob
UUID_META=$(cat /proc/sys/kernel/random/uuid)
RESPONSE=$(run_curl -w "\n%{http_code}" -X POST "$API_URL/transfers" -H "Authorization: Bearer $ALICE_TOKEN" -H "Idempotency-Key: $UUID_META" -d '{"recipient_email":"bob@test.com","amount":"5.00","metadata":{"description":"Test payment"}}')
HTTP_CODE=$(echo "$RESPONSE" | tail -1)
assert_http_code "200" "$HTTP_CODE" "Transfer with metadata"
RESPONSE=$(run_curl -X GET "$API_URL/wallet/transactions" -H "Authorization: Bearer $ALICE_TOKEN")
if echo "$RESPONSE" | jq -e '.[] | select(.metadata.description == "Test payment")' > /dev/null 2>&1; then
    TESTS_TOTAL=$((TESTS_TOTAL + 1))
    TESTS_PASSED=$((TESTS_PASSED + 1))
    echo "  ✓ Metadata persisted"
else
    TESTS_TOTAL=$((TESTS_TOTAL + 1))
    TESTS_FAILED=$((TESTS_FAILED + 1))
    echo "  ✗ Metadata not found"
fi

echo "\n==================================================================="
echo ">>> FINAL STATE VALIDATION"
echo "==================================================================="
php artisan tinker --execute="
\$alice = \App\Infrastructure\Persistence\Eloquent\User::where('email', 'alice@test.com')->first();
\$bob = \App\Infrastructure\Persistence\Eloquent\User::where('email', 'bob@test.com')->first();
echo json_encode([
    'users' => \App\Infrastructure\Persistence\Eloquent\User::count(),
    'wallets' => \App\Infrastructure\Persistence\Eloquent\Wallet::count(),
    'transactions' => \App\Infrastructure\Persistence\Eloquent\Transaction::count(),
    'events' => \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent::count(),
    'alice_balance' => \$alice->wallet->balance_cents, // Expected 60000 (0 + 100000 + 5000 + 5000 - 20000 - 30000)
    'bob_balance' => \$bob->wallet->balance_cents     // Expected 30000 (0 + 30000)
]);
" | jq '.'

echo "\n==================================================================="
echo "✓ E2E TEST SUITE COMPLETE"
echo "==================================================================="
echo "\nTest Results:"
echo "  Total:  $TESTS_TOTAL"
echo "  Passed: $TESTS_PASSED" 
echo "  Failed: $TESTS_FAILED"
[ "$TESTS_FAILED" -eq 0 ] && echo "\n✓ ALL TESTS PASSED!" || echo "\n✗ SOME TESTS FAILED"
echo "==================================================================="
