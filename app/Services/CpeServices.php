<?php

namespace App\Services;

use App\Models\Facturacion\Cpe;
use App\Models\Facturacion\CpeSerie;
use Exception;

class CpeServices
{
    // Obtener el CPE correspondiente al tipo de comprobante
    public function SendCep($cpeSerie, $cliente, $pos_order ,$tipo_de_nota = null, $nota = null)
    
    {
        // Lógica para enviar el CPE al api que coonfigure pra esta tienda 
        // tienda->api_url_facturador u tienda->token_facturador,
        // cada usiario tien su tienda asignada, asi sabemos que tienda usarr (user->tienda_id)
        
        $ruta = $pos_order->tienda->ruta_api_facturacion;
        $token = $pos_order->tienda->token_facturacion;
        $porcentaje_de_igv = 18;
        $total = $pos_order->total_amount;

        // Calcular el valor sin IGV
        $total_gravada = round($total / (1 + ($porcentaje_de_igv / 100)), 2);

        // El IGV se calcula como la diferencia (sin round adicional)
        $total_igv = $total - $total_gravada;
       
        // Verificación
        $suma_verificacion = $total_gravada + $total_igv; // Siempre será igual a $total
        if ($suma_verificacion != $total) {
            throw new \Exception("Error en el cálculo del total: $suma_verificacion no es igual a $total");
        }


        // ahora adecuamos el array de items con los siguiente datos alguno ya viene en $pos_order_lines
        /*
           array(
                    "unidad_de_medida"          => "ZZ",
                    "codigo"                    => "001",
                    "descripcion"               => "DETALLE DEL SERVICIO",
                    "cantidad"                  => "5",
                    "valor_unitario"            => "20",
                    "precio_unitario"           => "23.60",
                    "descuento"                 => "",
                    "subtotal"                  => "100",
                    "tipo_de_igv"               => "1",
                    "igv"                       => "18",
                    "total"                     => "118",
                    "anticipo_regularizacion"   => "false",
                    "anticipo_documento_serie"  => "",
                    "anticipo_documento_numero" => ""

                )
        */

        $sunat_transaction = 1; // Asumiendo que es una venta normal
        $porcentaje_de_igv_string = "18.00"; // Porcentaje de IGV como string
        // Inicializar array para items
        $pos_order_lines = [];
        $total_gravada = 0;
        $total_exonerada = 0;
        $total_inafecta = 0;
        $total_igv = 0;

        foreach ($pos_order->orderLines as $line) {
            $precio_total_linea = $line['price'] * $line['quantity']; // Total de la línea
            $subtotal = $line->producto->tipo_de_igv == 1 ?  round($precio_total_linea / (1 + ($porcentaje_de_igv / 100)), 2) : $precio_total_linea;
            $igv = $line->producto->tipo_de_igv == 1 ? round($precio_total_linea - $subtotal, 2) : 0  ; // IGV solo si es gravado
            $valor_unitario = round($subtotal / $line['quantity'], 2);

            $pos_order_lines[] = [
                'unidad_de_medida' => 'NIU',
                'codigo' => '',
                'descripcion' => $line->producto->nombre,
                'cantidad' => $line['quantity'],
                'valor_unitario' => $valor_unitario,
                'precio_unitario' => $line['price'],
                'descuento' => "",
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $precio_total_linea,
                'tipo_de_igv' => $line->producto->tipo_de_igv,
                'anticipo_regularizacion' => false,
                'anticipo_documento_serie' => '',
                'anticipo_documento_numero' => ''
            ];
            if ($line->producto->tipo_de_igv == 1) {
                $total_gravada += $subtotal;
                $total_igv += $igv;
            } elseif ($line->producto->tipo_de_igv == 8) {
                $total_exonerada += $precio_total_linea;
            } elseif ($line->producto->tipo_de_igv == 9) {
                $total_inafecta += $precio_total_linea;
            } elseif ($line->producto->tipo_de_igv == 16) {
                $total_gravada += $precio_total_linea; // Tipo de IGV 16 (exportacion)
                $pos_order->moneda = 2;
                $sunat_transaction = 2; // Cambiar a exportación
                $porcentaje_de_igv_string = "0.00"; // Sin IGV para exportación
            } else {
                throw new Exception("Tipo de IGV no soportado: " . $line->producto->tipo_de_igv);
            }
            
        }
        //dd($total_gravada, $total_exonerada, $total_inafecta);
        // Validar 
        // Validar (usando el array procesado)
        $suma_subtotales = array_sum(array_column($pos_order_lines, 'subtotal'));
        $suma_igv = array_sum(array_column($pos_order_lines, 'igv'));
        $suma_totales = array_sum(array_column($pos_order_lines, 'total'));

        if (abs(($suma_subtotales + $suma_igv) - $suma_totales) > 0.01) {
            throw new Exception("Error en cálculos: Subtotal + IGV no coincide con Total");
        }
        //manejar el tipo de comprovante , se encesita de esta manera
       /*  Tipo de COMPROBANTE que desea generar:
        1 = FACTURA
        2 = BOLETA
        3 = NOTA DE CRÉDITO
        4 = NOTA DE DÉBITO */
        // lo tenog de esta manera  01 = FACTURA, 03 = BOLETA
        // 05 = NOTA DE CRÉDITO, 06 = NOTA DE DÉBITO

        // Preparar los datos para enviar a NUBEFACT
        if ($cpeSerie->codigo_tipo_comprobante == '01') {
            $tipo_de_comprobante = 1; // Factura
        } elseif ($cpeSerie->codigo_tipo_comprobante == '03') {
            $tipo_de_comprobante = 2; // Boleta
        } elseif ($cpeSerie->codigo_tipo_comprobante == '07') {
            $tipo_de_comprobante = 3; // Nota de crédito
        } elseif ($cpeSerie->codigo_tipo_comprobante == '08') {
            $tipo_de_comprobante = 4; // Nota de débito
        } else {
            throw new Exception("Tipo de comprobante no soportado: " . $cpeSerie->codigo_tipo_comprobante);
        }
        //preparar los datos para nota de credito y nota de debito
        
        if ($cpeSerie->codigo_tipo_comprobante == '07' || $cpeSerie->codigo_tipo_comprobante == '08') {
            $tipo_documento_a_modificar = $pos_order->cpe->tipo_comprobante; // tipo de comprobante a modificar
            $documento_que_se_modifica_serie = $pos_order->cpe->serie;
            $documento_que_se_modifica_numero = $pos_order->cpe->numero;
            if ($cpeSerie->codigo_tipo_comprobante == '07'){
                $tipo_de_nota_de_credito = $tipo_de_nota;
                $tipo_de_nota_de_debito = "";
            }else {
                $tipo_de_nota_de_credito = "";
                $tipo_de_nota_de_debito = $tipo_de_nota;
            }

        } else {
            $tipo_documento_a_modificar = ""; // No aplica para factura o boleta
            $documento_que_se_modifica_serie = "";
            $documento_que_se_modifica_numero = "";
            $tipo_de_nota_de_credito = "";
            $tipo_de_nota_de_debito = "";
        }

        $data = array(
            "operacion"                         => "generar_comprobante",
            "tipo_de_comprobante"               => $tipo_de_comprobante, // 1: Factura, 2: Boleta, 3: Nota de crédito, 4: Nota de débito
            "serie"                             => $cpeSerie->serie,
            "numero"                            => $cpeSerie->correlativo,
            "sunat_transaction"                 => $sunat_transaction, // 1: Venta, 2: Exportación, 3: Retención, 4: Percepción
            "cliente_tipo_de_documento"         => $cliente->tipo_documento_identidad, // 1: DNI, 6: RUC, 7: Carnet de extranjería, 4: Pasaporte, etc.
            "cliente_numero_de_documento"       => $cliente->numero_documento_identidad,
            "cliente_denominacion"              => $cliente->nombre,
            "cliente_direccion"                 => $cliente->direccion,
            "cliente_email"                     => "",
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => date('d-m-Y'),
            "fecha_de_vencimiento"              => "",
            "moneda"                            => $pos_order->moneda,
            "tipo_de_cambio"                    => $pos_order->moneda == 1 ? "" : 3.556, // Asumiendo que 3.8 es el tipo de cambio para USD
            "porcentaje_de_igv"                 => $porcentaje_de_igv_string,
            "descuento_global"                  => "",
            "descuento_global"                  => "",
            "total_descuento"                   => "",
            "total_anticipo"                    => "",
            "total_gravada"                     => $total_gravada == 0 ? "" : $total_gravada,
            "total_inafecta"                    => $total_inafecta = 0 ? "" : $total_inafecta,
            "total_exonerada"                   => $total_exonerada = 0 ? "" : $total_exonerada,
            "total_igv"                         => round($total_igv, 2),
            "total_gratuita"                    => "",
            "total_otros_cargos"                => "",
            "total"                             => $pos_order->total_amount,
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "false",
            "observaciones"                     => "",
            "documento_que_se_modifica_tipo"    => $tipo_documento_a_modificar,
            "documento_que_se_modifica_serie"   => $documento_que_se_modifica_serie,
            "documento_que_se_modifica_numero"  => $documento_que_se_modifica_numero,
            "tipo_de_nota_de_credito"           => $tipo_de_nota_de_credito,
            "tipo_de_nota_de_debito"            => $tipo_de_nota_de_debito,
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "false",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "",
            "orden_compra_servicio"             => "",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "",
            /* "items" => array(
                array(
                    "unidad_de_medida"          => "NIU",
                    "codigo"                    => "001",
                    "descripcion"               => "DETALLE DEL PRODUCTO",
                    "cantidad"                  => "1",
                    "valor_unitario"            => "500",
                    "precio_unitario"           => "590",
                    "descuento"                 => "",
                    "subtotal"                  => "500",
                    "tipo_de_igv"               => "1",
                    "igv"                       => "90",
                    "total"                     => "590",
                    "anticipo_regularizacion"   => "false",
                    "anticipo_documento_serie"  => "",
                    "anticipo_documento_numero" => ""
                ),
                array(
                    "unidad_de_medida"          => "ZZ",
                    "codigo"                    => "001",
                    "descripcion"               => "DETALLE DEL SERVICIO",
                    "cantidad"                  => "5",
                    "valor_unitario"            => "20",
                    "precio_unitario"           => "23.60",
                    "descuento"                 => "",
                    "subtotal"                  => "100",
                    "tipo_de_igv"               => "1",
                    "igv"                       => "18",
                    "total"                     => "118",
                    "anticipo_regularizacion"   => "false",
                    "anticipo_documento_serie"  => "",
                    "anticipo_documento_numero" => ""

                )
            ) */
            "items" => $pos_order_lines
        );
        $data_json = json_encode($data);
       dd($data_json);
                /*
        #########################################################
        #### PASO 3: ENVIAR EL ARCHIVO A NUBEFACT ####
        +++++++++++++++++++++++++++++++++++++++++++++++++++++++
        # SI ESTÁS TRABAJANDO CON ARCHIVO JSON
        # - Debes enviar en el HEADER de tu solicitud la siguiente lo siguiente:
        # Authorization = Token token="8d19d8c7c1f6402687720eab85cd57a54f5a7a3fa163476bbcf381ee2b5e0c69"
        # Content-Type = application/json
        # - Adjuntar en el CUERPO o BODY el archivo JSON o TXT
        # SI ESTÁS TRABAJANDO CON ARCHIVO TXT
        # - Debes enviar en el HEADER de tu solicitud la siguiente lo siguiente:
        # Authorization = Token token="8d19d8c7c1f6402687720eab85cd57a54f5a7a3fa163476bbcf381ee2b5e0c69"
        # Content-Type = text/plain
        # - Adjuntar en el CUERPO o BODY el archivo JSON o TXT
        +++++++++++++++++++++++++++++++++++++++++++++++++++++++
        */
       //dd($data_json);
        //Invocamos el servicio de NUBEFACT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ruta);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Authorization: Token token="' . $token . '"',
                'Content-Type: application/json',
            )
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta  = curl_exec($ch);
        $respuesta = json_decode($respuesta, true);
        curl_close($ch);
        
        if (isset($respuesta['errors'])) {
            throw new Exception("Error al enviar el CPE: " . $respuesta['errors']
                        . " - Mensaje: " . $data_json);
        }
        $this->storeCpeResponseData($respuesta, $pos_order->id, $nota);
        return $respuesta;
    }

    private function storeCpeResponseData($respuesta,$pos_order_id, $nota = null){
        //dd($respuesta);
        $cpe = new Cpe();
        if ($nota !== null) {
            $cpe->comprobante_modificado_id = $pos_order_id; // Referencia a la nota de crédito o débito
            $pos_order_id = $nota->id; // Usar el ID de la nota como pos_order_id
        } else {
            $cpe->comprobante_modificado_id = null; // No hay comprobante modificado
        }
        $cpe->pos_order_id = $pos_order_id;
        $cpe->tipo_comprobante = $respuesta['tipo_de_comprobante'];
        $cpe->serie = $respuesta['serie'];
        $cpe->numero = $respuesta['numero'];
        $cpe->enlace = $respuesta['enlace'];
        $cpe->enlace_del_pdf = $respuesta['enlace_del_pdf'] ?? null;
        $cpe->enlace_del_xml = $respuesta['enlace_del_xml'] ?? null;
        $cpe->enlace_del_cdr = $respuesta['enlace_del_cdr'] ?? null;
        $cpe->aceptada_por_sunat = $respuesta['aceptada_por_sunat'] ?? false;
        $cpe->sunat_description = $respuesta['sunat_description'] ?? null;
        $cpe->sunat_note = $respuesta['sunat_note'] ?? null;
        $cpe->sunat_responsecode = $respuesta['sunat_responsecode'] ?? '0';
        $cpe->sunat_soap_error = $respuesta['sunat_soap_error'] ?? null;
        $cpe->cadena_para_codigo_qr = $respuesta['cadena_para_codigo_qr'] ?? null;
        $cpe->codigo_hash = $respuesta['codigo_hash'] ?? null;
        $cpe->save();
    }

    // nota de credito
    
}
