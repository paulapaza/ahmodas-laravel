<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Marca;
use App\Models\Inventario\Producto;
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
            'estado' => 1,
        ]);
        //crear categoria por defecto
        Categoria::create([
            'nombre' => 'Sin Categoria',
            'estado' => 1,
        ]);
        Producto::factory()->count(20)->create();
        // cliente
        Cliente::create([
            'nombre' => 'Cliente Varios',
            'tipo_documento_identidad' => 'DNI', // DNI
            'numero_documento_identidad' => '00000000',
            'nacionalidad' => 'peruana',
            'pais' => 'Perú',
            'ubigeo' => '000000',
            'direccion' => 's/d',
            'email' => '',
            'telefono' => '',
            
        ]);
        // cliente ruc
        Cliente::create([
            'nombre' => 'COMPAÑIA MINERA ANTAMINA S.A.',
            'tipo_documento_identidad' => 'RUC', // RUC
            'numero_documento_identidad' => '20330262428',
            'nacionalidad' => 'peruana',
            'pais' => 'Perú',
            'ubigeo' => '000000',
            'direccion' => 'Av. el Derby Nro. 055 Dpto. 801 (Torre 1),Santiago de Surco,Lima',
            'email' => 'antamina♠4mail.com',
            'telefono' => '999888777',
          
        ]);

        //tienda
        \App\Models\Inventario\Tienda::create([
            'nombre' => 'virrey 01',
            'direccion' => 'Av. Principal 123',
            'telefono' => '987654321',
            'estado' => 1,
        ]);
        // tienda 02 rey
        \App\Models\Inventario\Tienda::create([
            'nombre' => 'rey 02',
            'direccion' => 'Av. Secundaria 456',
            'telefono' => '123456789',
            'estado' => 1,
        ]);
        // tienda 03

        // CpeSerie
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '12', // Ticket
            'serie' => 'TT01',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '01', // Factura
            'serie' => 'FF01',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '03', // Boleta
            'serie' => 'BB01',      
            'correlativo' => 1,
            'estado' => 1,
        ]);
        //para tienda 02
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '12', // Ticket
            'serie' => 'TT02',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '01', // Factura
            'serie' => 'FF02',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        \App\Models\Facturacion\CpeSerie::create([      
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '03', // Boleta
            'serie' => 'BB02',
            'correlativo' => 1,
            'estado' => 1,
        ]);

    }
}
