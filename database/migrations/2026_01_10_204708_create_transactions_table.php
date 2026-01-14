<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            // Simple auto-increment ID (read model, not exposed)
            $table->id();
            $table->char('wallet_id', 36);
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_out', 'transfer_in']);
            $table->bigInteger('amount_cents');
            $table->bigInteger('balance_after_cents');

            // Transfer-related fields
            $table->string('related_user_email')->nullable();
            $table->bigInteger('related_transaction_id')->nullable();

            // Idempotency (UUID for deduplication)
            $table->char('idempotency_key', 36)->unique();

            // Metadata for audit trail
            $table->json('metadata')->nullable();

            $table->timestamp('created_at');

            // Foreign keys and indexes
            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
            $table->foreign('related_transaction_id')->references('id')->on('transactions')->nullOnDelete();
            $table->index(['wallet_id', 'created_at']);
            $table->index('type');
            $table->index('idempotency_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
