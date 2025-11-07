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
        Schema::create('salida_productos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('tienda_id');

            $table->integer('stock_antes');
            $table->integer('stock_despues');
            $table->integer('cantidad_reducida');
            $table->tinyInteger('tipo')->nullable()->comment('1 = salida manual, 2 = salida por venta, 3 = ingreso manual, 4: ingreso por anulacion');
            $table->unsignedBigInteger('pos_order_id')->nullable()->comment('ID de la venta asociada');
            $table->string('comentario')->nullable();
            $table->json('producto_datos')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salida_productos');
    }
};
