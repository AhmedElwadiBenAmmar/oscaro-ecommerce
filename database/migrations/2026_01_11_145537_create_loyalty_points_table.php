<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('available_points')->default(0);
            $table->integer('used_points')->default(0);
            $table->integer('expired_points')->default(0);
            $table->timestamps();

            $table->index('total_points');
            $table->index('available_points');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
    }
};
