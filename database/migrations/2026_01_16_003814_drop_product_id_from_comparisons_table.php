<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comparisons', function (Blueprint $table) {
            if (Schema::hasColumn('comparisons', 'product_id')) {
                // Supprimer la contrainte de clé étrangère + index
                $table->dropForeign('comparisons_product_id_foreign');
                $table->dropColumn('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comparisons', function (Blueprint $table) {
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('pieces');
        });
    }
};
