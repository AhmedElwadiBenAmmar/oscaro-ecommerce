<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_plate_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->integer('lookup_count')->default(1);
            $table->timestamp('last_lookup_at')->nullable();
            $table->string('country_code', 2)->default('FR');
            $table->json('api_response')->nullable();
            $table->boolean('is_successful')->default(false);
            $table->timestamps();

            $table->index('license_plate');
            $table->index('vehicle_id');
            $table->index('last_lookup_at');
            $table->index('lookup_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_plate_lookups');
    }
};
