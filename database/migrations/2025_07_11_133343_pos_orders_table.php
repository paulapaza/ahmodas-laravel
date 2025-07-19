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
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->timestamp('order_date')->useCurrent();
            $table->string('tipo_comprobante', 20);
            $table->string('serie')->default('001');
            $table->decimal('total_amount', 10, 2);
            //moneda
            $table->TinyInteger('moneda'); // 1 for PEN, 2 for USD
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('tienda_id')->nullable();
            $table->string('estado')->default('completado');
            // tienda
            $table->timestamps();
            // Foreign keys
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('set null');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_order');
    }
};
