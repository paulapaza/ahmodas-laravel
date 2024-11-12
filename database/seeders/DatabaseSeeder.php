<?php

namespace Database\Seeders;

use App\Models\Inventario\Categoria;
use App\Models\Inventario\Marca;
use App\Models\User;
use Database\Seeders\catalogos\tipo_de_afectacion;
use Database\Seeders\catalogos\tipo_de_comprobante;
use Database\Seeders\catalogos\tipo_documento_identidad;
use Database\Seeders\catalogos\tipo_precio;
use Database\Seeders\facturacion\documento_serie_correlativo;
use Database\Seeders\facturacion\impuestos;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(empresa::class);
        $this->call(unidad_de_medida::class);
        $this->call(tipo_documento_identidad::class);
        $this->call(tipo_precio::class);
        $this->call(tipo_de_comprobante::class);
        $this->call(tipo_de_afectacion::class);
        $this->call(impuestos::class);
        $this->call(documento_serie_correlativo::class);

        User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin'),

        ]);
        //crear marca por defecto
        Marca::create([
            'nombre' => 'Sin Marca',
            'descripcion' => 'marca asignada por defecto',
            'estado' => 1,
        ]);
        //crear categoria por defecto
        Categoria::create([
            'nombre' => 'Sin Categoria',
            'descripcion' => 'categoria asignada por defecto',
            'estado' => 1,
        ]);
    }
}
