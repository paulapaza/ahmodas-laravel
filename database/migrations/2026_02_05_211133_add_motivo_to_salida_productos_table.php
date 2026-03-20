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
        Schema::table('salida_productos', function (Blueprint $table) {
            $table->string('motivo')->default('compra')->after('tipo')->comment('motivo de ingreso: compra, cambio, devolucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salida_productos', function (Blueprint $table) {
            $table->dropColumn('motivo');
        });
    }
};
