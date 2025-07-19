<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Productos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('codigo_barras', 13)->nullable(); // mejor que "barcode"
            $table->string('nombre', 100);

            // Precios y costo
            $table->decimal('costo_unitario', 10, 2)->nullable(); // decimal es mejor para dinero
            $table->decimal('precio_unitario', 10, 2);            // precio de venta estándar
            $table->decimal('precio_minimo', 10, 2);              // precio mínimo de venta

            // Relaciones
            $table->foreignId('marca_id')->constrained('marcas');
            $table->foreignId('categoria_id')->constrained('categorias');

            // tipo moneda
            $table->tinyInteger('moneda')->default(1); // 1 = PEN, 2 = USD

            // impuesto de venta
            $table->tinyInteger('tipo_de_igv')->default(1); // Porcentaje del impuesto de venta, por ejemplo, 18%

          
            // Estado del producto (activo/inactivo)
            $table->boolean('estado')->default(1); // tinyInteger también sirve, pero boolean es más claro

            // Auditoría
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
}
