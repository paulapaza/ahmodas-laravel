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
        Schema::create('facturacion_configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('impuesto_venta_predeterminado', 2)->nullable();
            $table->string('impuesto_compra_predeterminado', 2)->nullable();
            $table->string('moneda', 3)->nullable();
            $table->string('simbolo_moneda', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
