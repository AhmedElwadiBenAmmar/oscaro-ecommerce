<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_product_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('piece_id')->constrained('pieces')->onDelete('cascade');
            $table->string('interaction_type');
            $table->timestamp('interaction_date');
            $table->timestamps();

            $table->index(['user_id', 'piece_id']);
            $table->index('interaction_type');
            $table->index('interaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_product_interactions');
    }
};
