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
        Schema::create('ai_report_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID del usuario que realizó la consulta');
            $table->text('prompt')->comment('El texto exacto que escribió el usuario');
            $table->text('generated_sql')->nullable()->comment('El SQL generado por la IA');
            $table->boolean('is_successful')->default(false)->comment('Indica si la IA generó un SQL válido y se ejecutó sin errores');
            $table->text('error_message')->nullable()->comment('Mensaje de error si la ejecución falló o no se generó SQL');
            $table->integer('execution_time_ms')->nullable()->comment('Tiempo total de ejecución en milisegundos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_report_logs');
    }
};
