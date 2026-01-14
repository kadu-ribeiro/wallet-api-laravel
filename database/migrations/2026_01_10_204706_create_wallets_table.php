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
        Schema::create('wallets', function (Blueprint $table) {
            // UUID v7 as PRIMARY KEY (VARCHAR 36 for SQLite)
            $table->char('id', 36)->primary();
            $table->char('user_id', 36)->unique();
            $table->bigInteger('balance_cents')->default(0);
            $table->char('currency', 3)->default('BRL');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
