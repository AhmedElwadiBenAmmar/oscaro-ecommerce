<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_piece', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_engine_id')->constrained('vehicle_engines')->onDelete('cascade');
            $table->foreignId('piece_id')->constrained('pieces')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['vehicle_engine_id', 'piece_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_piece');
    }
};
