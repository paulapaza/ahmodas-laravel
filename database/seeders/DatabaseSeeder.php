<?php

namespace Database\Seeders;

use App\Console\Commands\CrearPermisos;
use App\Models\Cliente;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Marca;
use App\Models\Inventario\Producto;
use App\Models\User;

use Database\Seeders\facturacion\documento_serie_correlativo;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        /* $this->call(tipo_documento_identidad::class);
        $this->call(tipo_precio::class);
        $this->call(tipo_de_comprobante::class);
        $this->call(tipo_de_afectacion::class);
        $this->call(impuestos::class); */
        $this->call(documento_serie_correlativo::class);
       

        User::factory()->create([
            'name' => 'Administrador',
            'username' => 'Paul',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin'),

        ]);
        //otro usuario
        User::factory()->create([
            'name' => 'Mariluz Principal',
            'username' => 'tienda04',
            'tienda_id' => 1, // Asignar a la segunda tienda
            'email' => 'tienda04@gmail.com',
            'password' => bcrypt('12345678'),
        ]); 
         User::factory()->create([
            'name' => 'Mariluz Sucursal',
            'username' => 'tienda05',
            'tienda_id' => 2, // Asignar a la segunda tienda
            'email' => 'tienda05@gmail.com',
            'password' => bcrypt('12345678'),
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
        //Producto::factory()->count(20)->create();
        // cliente
        Cliente::create([
            'nombre' => 'Cliente Varios',
            'tipo_documento_identidad' => '1', // DNI
            'numero_documento_identidad' => '00000000',
            'ubigeo' => '000000',
            'direccion' => 's/d',
            'email' => '',
            'telefono' => '',
            
        ]);
        // cliente ruc
        Cliente::create([
            'nombre' => 'COMPAÑIA MINERA ANTAMINA S.A.',
            'tipo_documento_identidad' => '6', // RUC
            'numero_documento_identidad' => '20330262428',
            'ubigeo' => '000000',
            'direccion' => 'Av. el Derby Nro. 055 Dpto. 801 (Torre 1),Santiago de Surco,Lima',
            'email' => 'antamina♠4mail.com',
            'telefono' => '999888777',
          
        ]);

        //tienda
        \App\Models\Inventario\Tienda::create([
            'nombre' => 'Marialuz Principal',
            'direccion' => 'Av. Principal 123',
            'telefono' => '987654321',
            'estado' => 1,
            'ruta_api_facturacion' => 'https://api.nubefact.com/api/v1/e432a5db-c262-4b10-945d-cf2915b47b28', // Ruta de API de facturación
            'token_facturacion' => 'a7d1e5aa349f4a9880ffe3abb49335d1b91031823fb540f39bc01cbea474ca1d', // Token de API de facturación
        ]);
        // tienda 02 rey
        \App\Models\Inventario\Tienda::create([
            'nombre' => 'Marialuz Sucursal',
            'direccion' => 'Av. Secundaria 456',
            'telefono' => '123456789',
            'estado' => 1,
            'ruta_api_facturacion' => 'https://api.nubefact.com/api/v1/e432a5db-c262-4b10-945d-cf2915b47b28', // Ruta de API de facturación
            'token_facturacion' => 'f7f30bb2d25841c0b94b615f317c97abeb16317ca3b7415c8af3e1fbaea3a165', // Token de API de facturación
        ]);
       

        // para tienda 01

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
            'serie' => 'FFF1',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '03', // Boleta
            'serie' => 'BBB1',      
            'correlativo' => 1,
            'estado' => 1,
        ]);

        //notade credito
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '07', // Nota de Crédito
            'serie' => 'FCF1',
            'correlativo' => 1,
            'estado' => 1,
        ]);
         \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '07', // Nota de Crédito
            'serie' => 'BCB1',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        //notade debito para tienda 01
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '08', // Nota de Débito
            'serie' => 'FDF1',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        //
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 1, // Asignar a la primera tienda
            'codigo_tipo_comprobante' => '08', // Nota de Débito
            'serie' => 'BDB1',
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
            'serie' => 'FFF2',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        \App\Models\Facturacion\CpeSerie::create([      
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '03', // Boleta
            'serie' => 'BBB2',
            'correlativo' => 1,
            'estado' => 1,
        ]);

        //notade credito
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '07', // Nota de Crédito
            'serie' => 'FCF2',
            'correlativo' => 1,
            'estado' => 1,
        ]);
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '07', // Nota de Débito
            'serie' => 'BCB2',
            'correlativo' => 1,
            'estado' => 1,
        ]);


        //notade debito
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '08', // Nota de Débito
            'serie' => 'FDF2',
            'correlativo' => 1,
            'estado' => 1,
        ]);

        //notadebito para boletas
        \App\Models\Facturacion\CpeSerie::create([
            'tienda_id' => 2, // Asignar a la segunda tienda
            'codigo_tipo_comprobante' => '08', // Nota de Débito
            'serie' => 'BDB2',
            'correlativo' => 1,
            'estado' => 1,
        ]);

        // creado producto 2 exonerado (sal )e inafectos (libro)
        Producto::factory()->create([
            'codigo_barras' => '000001',
            'nombre' => 'Sal de mesa',
            'costo_unitario' => 1.00,
            'precio_unitario' => 1.20,
            'precio_minimo' => 1.00,
            'categoria_id' => 1,
            'marca_id' => 1,
            'estado' => 1,
            'tipo_de_igv' => 8, // Exonerado
        ]);
        Producto::factory()->create([
            'codigo_barras' => '000002',
            'nombre' => 'Libro de texto',
            'costo_unitario' => 5.00,
            'precio_unitario' => 6.00,
            'precio_minimo' => 5.00,
            'categoria_id' => 1,
            'marca_id' => 1,
            'estado' => 1,
            'tipo_de_igv' => 9, // Inafecto
        ]);  
        
        // crear roles
        $this->call(RolesSeeder::class);
        // ejecutar comando para crear permisos  php artisan app:crear-permisos
        //$this->call(CrearPermisos::class);

    }
}
