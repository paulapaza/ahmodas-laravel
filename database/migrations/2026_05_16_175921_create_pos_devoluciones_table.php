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
        Schema::create('pos_devoluciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tienda_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('tipo_movimiento', ['cambio', 'devolucion_dinero']);
            $table->decimal('monto_devolucion', 10, 2)->default(0);
            $table->decimal('monto_nuevo', 10, 2)->default(0);
            $table->decimal('monto_diferencia', 10, 2)->default(0);
            $table->string('metodo_pago')->default('Efectivo');
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('pos_devolucion_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_devolucion_id');
            $table->unsignedBigInteger('producto_id');
            $table->enum('tipo_item', ['devuelto', 'nuevo']);
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('pos_devolucion_id')->references('id')->on('pos_devoluciones')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_devolucion_detalles');
        Schema::dropIfExists('pos_devoluciones');
    }
};
