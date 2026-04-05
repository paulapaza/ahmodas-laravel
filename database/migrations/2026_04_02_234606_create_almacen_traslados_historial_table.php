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
        Schema::create('almacen_traslados_historial', function (Blueprint $table) {
            $table->id();

            // Relación
            $table->foreignId('almacen_traslado_id')->constrained('almacen_traslados')->onDelete('cascade');

            // Fotografía del stock en este punto del tiempo
            $table->integer('stock_vendido')->default(0);
            $table->integer('stock_disponible')->default(0);
            $table->integer('stock_almacen')->default(0);

            // Auditoría de Usuarios
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacen_traslados_historial');
    }
};
