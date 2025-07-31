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
        Schema::table('users', function (Blueprint $table) {
              $table->string('printer_ip')->nullable()->after('print_type');
              $table->string('printer_name')->nullable()->after('printer_ip');
              $table->string('restriccion_precio_minimo')->nullable()->after('printer_name');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('printer_ip');
            $table->dropColumn('printer_name');
        });
    }
};
