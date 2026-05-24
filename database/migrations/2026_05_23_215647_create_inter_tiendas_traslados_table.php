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
        Schema::create('inter_tiendas_traslados', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('tienda_origen_id')->constrained('tiendas');
            $table->foreignId('tienda_destino_id')->constrained('tiendas');
            $table->foreignId('producto_id')->constrained('productos');
            $table->date('fecha')->index();

            // Stock de Auditoría Origen (Antes y Después)
            $table->integer('stock_origen_anterior');
            $table->integer('stock_origen_posterior');

            // Stock de Auditoría Destino (Antes y Después)
            $table->integer('stock_destino_anterior');
            $table->integer('stock_destino_posterior');

            // Información del Traslado
            $table->integer('cantidad')->comment('Cantidad trasladada entre tiendas');
            
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
        Schema::dropIfExists('inter_tiendas_traslados');
    }
};
