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
        Schema::create('tiendas_traslados', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->foreignId('almacen_id')->constrained('tiendas');
            $table->foreignId('producto_id')->constrained('productos');
            $table->date('fecha')->index();

            // Stock de Auditoría (Antes de la operación)
            $table->integer('tienda_stock_anterior');
            $table->integer('almacen_stock_anterior');

            // Stock de Auditoría (Después de la operación)
            $table->integer('tienda_stock_posterior');
            $table->integer('almacen_stock_posterior');

            // Información del Traslado
            $table->integer('cantidad')->comment('Cantidad trasladada de la tienda al almacén');
            
            // Auditoría de Usuarios
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiendas_traslados');
    }
};
