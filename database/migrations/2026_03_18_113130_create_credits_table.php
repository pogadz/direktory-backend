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
        Schema::create('credits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->integer('amount'); // signed (+ / -)

            $table->string('action_type'); // TOPUP, DEDUCT, REFUND
            $table->string('transaction_type'); // BOOKING, PAYMENT

            $table->foreignId('transaction_id')->nullable()
                ->constrained()->nullOnDelete();

            $table->nullableMorphs('reference');

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
