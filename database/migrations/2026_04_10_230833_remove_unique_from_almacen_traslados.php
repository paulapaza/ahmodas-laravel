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
        Schema::table('almacen_traslados', function (Blueprint $table) {
            $table->dropUnique(['tienda_id', 'producto_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('almacen_traslados', function (Blueprint $table) {
            $table->unique(['tienda_id', 'producto_id', 'fecha']);
        });
    }
};
