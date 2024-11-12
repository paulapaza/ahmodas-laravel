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
        //id	order_id	item	produc_name	cantidad	valor_unitario	precio_unitario	igv	porcentaje_igv	valor_total	importe_total	

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoice')->restrictOnUpdate()->restrictOnDelete();
            $table->integer('item');
            $table->string('produc_name', 254);
            $table->string('unidad_de_medida',12);
            $table->decimal('cantidad',10,2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('porcentaje_igv', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->decimal('importe_total', 10, 2);
            $table->timestamps();
              // Llave foránea para order_id
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //borramos la tabla
        Schema::dropIfExists('invoice_lines');

    }
};
