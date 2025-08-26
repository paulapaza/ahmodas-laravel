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
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'sale_token')) {
                $table->uuid('sale_token')->nullable()->after('id');
            }
            $table->unique('sale_token');
            $table->unique(['serie', 'order_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'sale_token')) {
                $table->dropUnique(['sale_token']);
                $table->dropColumn('sale_token');
            }
            try {
                $table->dropUnique(['serie', 'order_number']);
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};
