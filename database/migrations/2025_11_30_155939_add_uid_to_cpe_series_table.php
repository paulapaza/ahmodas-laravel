<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    // 1. Agregar columna como nullable
    Schema::table('cpe_series', function (Blueprint $table) {
      $table->ulid('uid')->nullable()->after('id');
    });

    // 2. Rellenar todos los registros existentes con ULID
    DB::table('cpe_series')->whereNull('uid')->chunkById(100, function ($rows) {
      foreach ($rows as $row) {
        DB::table('cpe_series')
          ->where('id', $row->id)
          ->update(['uid' => Str::ulid()->toString()]);
      }
    });

    // 3. Hacer la columna unique() sin quitar nullable
    Schema::table('cpe_series', function (Blueprint $table) {
      $table->unique('uid');
    });
  }

  public function down(): void
  {
    Schema::table('cpe_series', function (Blueprint $table) {
      $table->dropUnique(['uid']);
      $table->dropColumn('uid');
    });
  }
};
