<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points_required');
            $table->string('type'); // 'discount', 'free_shipping', 'gift', 'voucher'
            $table->decimal('value', 10, 2)->nullable(); // Valeur de la récompense
            $table->integer('stock')->nullable(); // NULL = illimité
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('image')->nullable();
            $table->json('conditions')->nullable(); // Conditions spécifiques
            $table->timestamps();

            $table->index('points_required');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_rewards');
    }
};
