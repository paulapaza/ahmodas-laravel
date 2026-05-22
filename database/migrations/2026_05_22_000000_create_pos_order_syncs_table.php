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
        Schema::create('pos_order_syncs', function (Blueprint $table) {
            $table->id();
            
            // Relación 1-a-1 única con la orden de venta local
            $table->unsignedBigInteger('pos_order_id')->unique();
            
            // El payload completo que se envió o se enviará
            $table->json('payload');
            
            // Estado de la sincronización: éxito o fallo
            $table->enum('status', ['success', 'failed'])->default('failed');
            
            // Información técnica en caso de fallar
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            
            // Contador de intentos de envío
            $table->integer('attempts')->default(1);
            
            $table->timestamps();

            // Llave foránea hacia pos_orders
            $table->foreign('pos_order_id')
                  ->references('id')
                  ->on('pos_orders')
                  ->onDelete('cascade');

            // Índices para optimizar las búsquedas
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_order_syncs');
    }
};
