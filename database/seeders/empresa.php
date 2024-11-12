<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class empresa extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          
        DB::table('empresa')->insert([
            'nombre_comercial' => 'Empresa de Prueba',
            'descripcion_comercial' => 'Empresa de Prueba',
            'direccion_comercial' => 'Direccion de Prueba',
            'telefono' => '123456789',
            'email' => 'example@ejemplo.com',
            'logo' => '150.png',
            'website' => 'www.example.com',
            'facturacion_electronica' => 'NO',


            'pais'=> 'PE',
            'estado' => 'activo',

            'razon_social' => 'Mi Empresa S.A.C',
            'tipo_documento' => '6',
            'nro_documento' => '12345678901',
            'direccion_fiscal' => 'Calle de ejemplo 425',
            'departamento' => 'Arequipa',
            'provincia' => 'Arequipa',
            'distrito' => 'Arequipa',
            'ubigeo' => '040101',

            'soap_tipo' => 'demo',
            'soap_envio' => 'sunat',
            'soap_usuario' => 'MODDATOS',
            'soap_clave_usuario' => 'MODDATOS'

            
        ]);
    }
}
