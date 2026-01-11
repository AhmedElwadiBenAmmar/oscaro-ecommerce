<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('pieces', function (Blueprint $table) {
        if (!Schema::hasColumn('pieces', 'is_active')) {
            $table->boolean('is_active')->default(true);
        }

        if (!Schema::hasColumn('pieces', 'stock')) {
            $table->integer('stock')->default(0);
        }
    });
}


    public function down(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            if (Schema::hasColumn('pieces', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('pieces', 'stock')) {
                $table->dropColumn('stock');
            }
        });
    }
};
