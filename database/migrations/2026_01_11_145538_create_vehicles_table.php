<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('make'); // Marque
            $table->string('model'); // Modèle
            $table->integer('year'); // Année
            $table->string('fuel_type')->nullable();
            $table->string('engine_size')->nullable();
            $table->string('engine_code')->nullable();
            $table->string('transmission')->nullable();
            $table->string('body_type')->nullable();
            $table->string('vin')->nullable()->unique();
            $table->timestamps();

            $table->index('make');
            $table->index('model');
            $table->index('year');
            $table->index(['make', 'model', 'year']);
            $table->index('vin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
