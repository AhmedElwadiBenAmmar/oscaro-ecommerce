<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points');
            $table->string('type'); // 'order', 'reward', 'manual', 'expiration', 'cancellation'
            $table->enum('operation', ['credit', 'debit']);
            $table->unsignedBigInteger('related_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('balance_after');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('expired')->default(false);
            $table->boolean('cancelled')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('reward_data')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
            $table->index('expires_at');
            $table->index(['expired', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
