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
        Schema::create('almacen_traslados', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('almacen_id')->constrained('tiendas');
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->foreignId('producto_id')->constrained('productos');

            // Stock de Auditoría (Antes de la operación)
            $table->integer('almacen_stock_anterior');
            $table->integer('tienda_stock_anterior');

            // Stock de Auditoría (Después de la operación)
            $table->integer('almacen_stock_posterior');
            $table->integer('tienda_stock_posterior');

            // Información del Traslado
            $table->integer('stock_vendido')->default(0);
            $table->integer('stock_disponible')->comment('Stock que aún no se ha vendido');
            $table->unique(['tienda_id', 'producto_id']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacen_traslados');
    }
};
