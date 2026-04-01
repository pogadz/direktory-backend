<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->timestamp('user_last_read_at')->nullable();
            $table->timestamp('profile_last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'user_id', 'profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
