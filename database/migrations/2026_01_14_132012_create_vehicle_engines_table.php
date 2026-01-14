<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_engines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models')->onDelete('cascade');
            $table->string('name');        // 1.6 HDi 110
            $table->integer('displacement')->nullable(); // en cm3
            $table->integer('power_hp')->nullable();
            $table->string('fuel_type')->nullable(); // diesel, essence
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_engines');
    }
};
