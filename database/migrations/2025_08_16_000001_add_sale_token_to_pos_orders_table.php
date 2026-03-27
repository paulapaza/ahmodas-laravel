<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            // 1. columna nullable (por datos viejos)
            if (!Schema::hasColumn('pos_orders', 'sale_token')) {
                $table->uuid('sale_token')->nullable()->after('id');
            }
        });

        // 2. rellena los nulos con UUID
        DB::table('pos_orders')
            ->whereNull('sale_token')
            ->update(['sale_token' => DB::raw('UUID()')]);

        // 3. ahora sí, NOT NULL + índices únicos
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->uuid('sale_token')->nullable(false)->change();
            $table->unique('sale_token');
            $table->unique(['serie', 'order_number']);
        });
    }

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
