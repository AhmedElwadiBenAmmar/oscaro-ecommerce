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
        // database/migrations/xxxx_create_reviews_table.php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('order_id')->nullable()->constrained();
    $table->integer('rating'); // 1-5
    $table->string('title');
    $table->text('comment');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->foreignId('moderated_by')->nullable()->constrained('users');
    $table->timestamp('moderated_at')->nullable();
    $table->text('moderation_reason')->nullable();
    $table->boolean('verified_purchase')->default(false);
    $table->timestamps();
    $table->unique(['user_id', 'product_id']);
    $table->index(['product_id', 'status']);
});



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
