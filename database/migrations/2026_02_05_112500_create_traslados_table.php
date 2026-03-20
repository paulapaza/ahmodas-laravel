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
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tienda_origen_id');
            $table->unsignedBigInteger('tienda_destino_id');
            $table->unsignedBigInteger('user_id');
            $table->string('codigo', 20)->unique();
            $table->string('comentario')->nullable();
            $table->timestamps();
        });

        Schema::table('salida_productos', function (Blueprint $table) {
            $table->unsignedBigInteger('traslado_id')->nullable()->after('pos_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salida_productos', function (Blueprint $table) {
            $table->dropColumn('traslado_id');
        });
        Schema::dropIfExists('traslados');
    }
};
