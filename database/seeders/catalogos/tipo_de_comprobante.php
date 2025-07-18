<?php

namespace Database\Seeders\catalogos;



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class tipo_de_comprobante extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        01 Factura
        03 Boleta de venta
        06 Carta de porte aéreo
        07 Nota de crédito
        08 Nota de débito
        09 Guia de remisión remitente
        12 Ticket de maquina registradora
        13 Documento emitido por bancos, instituciones financieras, crediticias y de seguros que se encuentren
        bajo el control de la superintendencia de banca y seguros
        14 Recibo de servicios públicos
        15 Boletos emitidos por el servicio de transporte terrestre regular urbano de pasajeros y el ferroviario
        público de pasajeros prestado en vía férrea local.
        16 Boleto de viaje emitido por las empresas de transporte público interprovincial de pasajeros
        18 Documentos emitidos por las afp
        20 Comprobante de retencion
        21 Conocimiento de embarque por el servicio de transporte de carga marítima
        24 Certificado de pago de regalías emitidas por perupetro s.a.
        31 Guía de remisión transportista
        37 Documentos que emitan los concesionarios del servicio de revisiones técnicas
        40 Comprobante de percepción
        41 Comprobante de percepción – venta interna (físico - formato impreso)
        43 Boleto de compañias de aviación transporte aéreo no regular
        45 Documentos emitidos por centros educativos y culturales, universidades, asociaciones y fundaciones.
        56 Comprobante de pago seae
        71 Guia de remisión remitente complementaria
        72 Guia de remisión transportista complementaria
        */
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '01',
            'codigo_nubefact' => '1',
            'descripcion' => 'Factura',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '03',
            'codigo_nubefact' => '2',
            'descripcion' => 'Boleta de venta',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '06',
            'descripcion' => 'Carta de porte aéreo',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '07',
            'codigo_nubefact' => '3',
            'descripcion' => 'Nota de crédito',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '08',
            'codigo_nubefact' => '4',
            'descripcion' => 'Nota de débito',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '09',
            'descripcion' => 'Guia de remisión remitente',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '12',
            'descripcion' => 'Ticket de maquina registradora',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '13',
            'descripcion' => 'Documento emitido por bancos, instituciones financieras, crediticias y de seguros que se encuentren bajo el control de la superintendencia de banca y seguros',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '14',
            'descripcion' => 'Recibo de servicios públicos',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '15',
            'descripcion' => 'Boletos emitidos por el servicio de transporte terrestre regular urbano de pasajeros y el ferroviario público de pasajeros prestado en vía férrea local.',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '16',
            'descripcion' => 'Boleto de viaje emitido por las empresas de transporte público interprovincial de pasajeros',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '18',
            'descripcion' => 'Documentos emitidos por las afp',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '20',
            'descripcion' => 'Comprobante de retencion',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '21',
            'descripcion' => 'Conocimiento de embarque por el servicio de transporte de carga marítima',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '24',
            'descripcion' => 'Certificado de pago de regalías emitidas por perupetro s.a.',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '31',
            'descripcion' => 'Guía de remisión transportista',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '37',
            'descripcion' => 'Documentos que emitan los concesionarios del servicio de revisiones técnicas',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '40',
            'descripcion' => 'Comprobante de percepción',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '41',
            'descripcion' => 'Comprobante de percepción – venta interna (físico - formato impreso)',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '43',
            'descripcion' => 'Boleto de compañias de aviación transporte aéreo no regular',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '45',
            'descripcion' => 'Documentos emitidos por centros educativos y culturales, universidades, asociaciones y fundaciones.',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '56',
            'descripcion' => 'Comprobante de pago seae',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '71',
            'descripcion' => 'Guia de remisión remitente complementaria',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_comprobante')->insert([
            'codigo' => '72',
            'descripcion' => 'Guia de remisión transportista complementaria',
            'estado' => 0,
        ]);
    }
}