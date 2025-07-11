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
        Schema::create('pos_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_orders_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
            // Foreign key constraints
            $table->foreign('pos_orders_id')->references('id')->on('pos_orders')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_order_lines');
        // --- IGNORE ---
    }
};
