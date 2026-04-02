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
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'almacen', 'guard_name' => 'web']);
        $role->givePermissionTo('ver-inventario');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo por defecto para roles, pero se puede remover si es necesario.
        // \Spatie\Permission\Models\Role::where('name', 'almacen')->delete();
    }
};
