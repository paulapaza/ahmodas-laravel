<?php

namespace App\Services;

use App\Models\Facturacion\Cpe;
use App\Models\Facturacion\CpeSerie;
use Exception;
use Illuminate\Support\Facades\Log;

class CpeServices
{
    // Obtener el CPE correspondiente al tipo de comprobante
    public function SendCep($cpeSerie, $cliente, $pos_order ,$tipo_de_nota = null, $nota = null, $tipo_venta=null)
    
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

            if ($tipo_venta == "exportacion" ) {
                // Para exportación o cliente con BUSINESS-ID, no se aplica IGV
                $tipo_de_igv = 16; // Tipo de IGV para exportación
            } elseif ($cliente->numero_documento_identidad == 'BUSINESS-ID'){
                $tipo_de_igv = "16"; // Tipo de IGV para BUSINESS-ID
            }
            else {
                $tipo_de_igv = $line->producto->tipo_de_igv;
            }

            $pos_order_lines[] = [
                'unidad_de_medida' => 'NIU',
                'codigo' => '',
                'descripcion' => $line->producto->nombre,
                'cantidad' => $line['quantity'],
                'valor_unitario' => $tipo_venta == "exportacion" || $cliente->numero_documento_identidad == 'BUSINESS-ID' ? $line['price'] : $valor_unitario,
                'precio_unitario' => $line['price'],
                'descuento' => "",
                'subtotal' => $tipo_venta == "exportacion" || $cliente->numero_documento_identidad == 'BUSINESS-ID'  ? $precio_total_linea : $subtotal,
                'igv' => $tipo_venta == "exportacion" || $cliente->numero_documento_identidad == 'BUSINESS-ID'  ? 0 : $igv,
                'total' => $precio_total_linea,
                'tipo_de_igv' => $tipo_de_igv,
                'anticipo_regularizacion' => false,
                'anticipo_documento_serie' => '',
                'anticipo_documento_numero' => ''
            ];

           //dd($cliente->numero_documento_identidad);
            if ($tipo_venta == "exportacion") {
                $total_inafecta += $precio_total_linea; // Tipo de IGV 16 (exportacion)
                $sunat_transaction = 2; // Cambiar a exportación
                $cliente->tipo_documento_identidad = 0; // RUC para exportación
                $line->producto->tipo_de_igv = 16; // Asignar tipo de IGV para exportación

            } elseif( $cliente->numero_documento_identidad == 'BUSINESS-ID'){
                $sunat_transaction = 2; // Cambiar a exportación
                $total_inafecta += $precio_total_linea; // Tipo de IGV 16 (exportacion)
            }
            elseif ($line->producto->tipo_de_igv == 1) {
                $total_gravada += $subtotal;
                $total_igv += $igv;
            } elseif ($line->producto->tipo_de_igv == 8) {
                $total_exonerada += $precio_total_linea;
            } elseif ($line->producto->tipo_de_igv == 9) {
                $total_inafecta += $precio_total_linea;
            } else {
                throw new Exception("Tipo de IGV no soportado: " . $line->producto->tipo_de_igv);
            }
            
        }
        //dd($pos_order_lines);
        //dd($total_gravada, $total_exonerada, $total_inafecta);
        // Validar 
        // Validar (usando el array procesado)
        $suma_subtotales = array_sum(array_column($pos_order_lines, 'subtotal'));
        $suma_igv = array_sum(array_column($pos_order_lines, 'igv'));
        $suma_totales = array_sum(array_column($pos_order_lines, 'total'));
        //dd("Suma Subtotales: $suma_subtotales, Suma IGV: $suma_igv, Suma Totales: $suma_totales");
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
        $total_gravada =round($total_gravada, 2);
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
            "total_descuento"                   => "",
            "total_anticipo"                    => "",
            "total_gravada"                     => $total_gravada == 0 ? "" : $total_gravada,
            "total_inafecta"                    => $total_inafecta == 0 ? "" : $total_inafecta,
            "total_exonerada"                   => $total_exonerada == 0 ? "" : $total_exonerada,
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
       //dd($data_json);
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
        */        //dd($data_json);
        $tipo_comprobante = $cpeSerie->codigo_tipo_comprobante; // '01' o '03'
        $isBoleta = ($tipo_comprobante === '03');
        $isFactura = ($tipo_comprobante === '01');

        $logMsg = "Iniciando envío de CPE a Nubefact. Orden ID: {$pos_order->id}, Serie: {$cpeSerie->serie}, Número: {$cpeSerie->correlativo}, Tipo: {$tipo_comprobante}";
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($logMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($logMsg);
        }

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
        
        // Configurar timeouts para no trabar el servidor
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 segundos para establecer conexión
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);        // 15 segundos máximo para toda la transferencia
        
        $respuesta  = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($respuesta === false) {
            $errorMsg = "Error de cURL al enviar CPE (Orden ID: {$pos_order->id}): " . $curl_error;
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Error en la conexión a internet o de red: " . $curl_error);
        }

        $respuesta_decoded = json_decode($respuesta, true);
        
        if ($respuesta_decoded === null) {
            $errorMsg = "Respuesta de Nubefact no es un JSON válido (Orden ID: {$pos_order->id}): " . substr($respuesta, 0, 500);
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Error en la conexión de internet o formato de respuesta inválido.");
        }
        
        if (isset($respuesta_decoded['errors'])) {
            $errorMsg = "Nubefact retornó errores (Orden ID: {$pos_order->id}): " . $respuesta_decoded['errors'];
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Error al enviar el CPE: " . $respuesta_decoded['errors']);
        }
        
        $successMsg = "CPE enviado con éxito (Orden ID: {$pos_order->id}). Serie: {$respuesta_decoded['serie']}, Número: {$respuesta_decoded['numero']}";
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($successMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($successMsg);
        }

        $this->storeCpeResponseData($respuesta_decoded, $pos_order->id, $nota);
        return $respuesta_decoded;
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

    // consultacpe
    public function consultarEstadoCpe($cpe_id)
    {
        $cpe = Cpe::find($cpe_id);
        if (!$cpe) {
            throw new Exception("CPE no encontrado con ID: " . $cpe_id);
        }
        if (!$cpe->posOrder) {
            throw new \Exception("No se encontró la orden POS asociada al CPE con ID: " . $cpe_id);
        }

        if (!$cpe->posOrder->tienda) {
            throw new \Exception("No se encontró la tienda asociada a la orden POS del CPE con ID: " . $cpe_id);
        }


        // Lógica para consultar el CPE en la API de NUBEFACT
        $ruta = $cpe->posOrder->tienda->ruta_api_facturacion;
        $token = $cpe->posOrder->user->tienda->token_facturacion;
        /*
            {
                "operacion": "consultar_comprobante",
                "tipo_de_comprobante": "1",
                "serie": "FFF1",
                "numero": "1"
            } 
        */
        $data = array(
            "operacion" => "consultar_comprobante",
            "tipo_de_comprobante" => $cpe->tipo_comprobante,
            "serie" => $cpe->serie,
            "numero" => $cpe->numero
        );
        $data_json = json_encode($data);

        $tipo_comprobante = $cpe->tipo_comprobante; // '1' = Factura, '2' = Boleta en formato Nubefact
        $isBoleta = ($tipo_comprobante == 2);
        $isFactura = ($tipo_comprobante == 1);

        $logMsg = "Iniciando consulta de estado de CPE ID: {$cpe_id}. Serie: {$cpe->serie}, Número: {$cpe->numero}, Tipo: {$tipo_comprobante}";
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($logMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($logMsg);
        }

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
        
        // Timeout de conexión y transferencia
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $respuesta  = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            $errorMsg = "Error de cURL al consultar estado de CPE ID {$cpe_id}: " . $curl_error;
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Error en la conexión a internet al consultar CPE: " . $curl_error);
        }

        $respuesta_decoded = json_decode($respuesta, true);

        if ($respuesta_decoded === null) {
            $errorMsg = "Respuesta de consulta de CPE ID {$cpe_id} no es JSON válido.";
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Respuesta inválida al consultar CPE.");
        }

        $successMsg = "Consulta de CPE ID {$cpe_id} completada.";
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($successMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($successMsg);
        }

        return $respuesta_decoded;
    }

    // anulacion de CPE- comunicar baja
    public function comunicarBaja($cpe_id, $motivo){
        /*
        {
            "operacion": "generar_anulacion",
            "tipo_de_comprobante": "2",
            "serie": "BBB1",
            "numero": "1",
            "motivo": "ERROR DEL SISTEMA",
            "codigo_unico": ""
            }
        */
        $cpe = Cpe::find($cpe_id);
        if (!$cpe) {
            throw new Exception("CPE no encontrado con ID: " . $cpe_id);
        }
        if (!$cpe->posOrder) {
            throw new \Exception("No se encontró la orden POS asociada al CPE con ID: " . $cpe_id);
        }
        if (!$cpe->posOrder->tienda) {
            throw new \Exception("No se encontró la tienda asociada a la orden POS del CPE con ID: " . $cpe_id);
        }
        $ruta = $cpe->posOrder->tienda->ruta_api_facturacion;
        $token = $cpe->posOrder->user->tienda->token_facturacion;
        $data = array(
            "operacion" => "generar_anulacion",
            "tipo_de_comprobante" => $cpe->tipo_comprobante,
            "serie" => $cpe->serie,
            "numero" => $cpe->numero,
            "motivo" => $motivo,
            "codigo_unico" => ""
        );
        $data_json = json_encode($data);

        $tipo_comprobante = $cpe->tipo_comprobante; // '1' = Factura, '2' = Boleta en formato Nubefact
        $isBoleta = ($tipo_comprobante == 2);
        $isFactura = ($tipo_comprobante == 1);

        $logMsg = "Iniciando comunicación de baja de CPE ID: {$cpe_id}. Serie: {$cpe->serie}, Número: {$cpe->numero}, Tipo: {$tipo_comprobante}, Motivo: {$motivo}";
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($logMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($logMsg);
        }

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
        
        // Timeout de conexión y transferencia
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $respuesta  = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            $errorMsg = "Error de cURL al comunicar baja de CPE ID {$cpe_id}: " . $curl_error;
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Error en la conexión a internet al comunicar baja: " . $curl_error);
        }

        $respuesta_decoded = json_decode($respuesta, true);

        if ($respuesta_decoded === null) {
            $errorMsg = "Respuesta de comunicación de baja de CPE ID {$cpe_id} no es JSON válido.";
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Respuesta inválida al comunicar baja.");
        }

        $successMsg = "Comunicación de baja de CPE ID {$cpe_id} enviada. Respuesta: " . json_encode($respuesta_decoded);
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($successMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($successMsg);
        }

        return $respuesta_decoded;
    }
    // Consultar el estado de comunicacaion de baja
    public function consultarEstadoBaja($cpe_id)
    {
        $cpe = Cpe::find($cpe_id);
        if (!$cpe) {
            throw new Exception("CPE no encontrado con ID: " . $cpe_id);
        }
        if (!$cpe->posOrder) {
            throw new \Exception("No se encontró la orden POS asociada al CPE con ID: " . $cpe_id);
        }
        if (!$cpe->posOrder->tienda) {
            throw new \Exception("No se encontró la tienda asociada a la orden POS del CPE con ID: " . $cpe_id);
        }

        // Lógica para consultar el estado de baja en la API de NUBEFACT
        $ruta = $cpe->posOrder->tienda->ruta_api_facturacion;
        $token = $cpe->posOrder->user->tienda->token_facturacion;
      
        $data = array(
            "operacion" => "consultar_anulacion",
            "tipo_de_comprobante" => $cpe->tipo_comprobante,
            "serie" => $cpe->serie,
            "numero" => $cpe->numero
        );
        $data_json = json_encode($data);

        $tipo_comprobante = $cpe->tipo_comprobante; // '1' = Factura, '2' = Boleta en formato Nubefact
        $isBoleta = ($tipo_comprobante == 2);
        $isFactura = ($tipo_comprobante == 1);

        $logMsg = "Iniciando consulta de estado de baja de CPE ID: {$cpe_id}. Serie: {$cpe->serie}, Número: {$cpe->numero}, Tipo: {$tipo_comprobante}";
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($logMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($logMsg);
        }
        
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
        
        // Timeout de conexión y transferencia
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $respuesta  = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            $errorMsg = "Error de cURL al consultar estado de baja de CPE ID {$cpe_id}: " . $curl_error;
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Error en la conexión a internet al consultar estado de baja: " . $curl_error);
        }

        $respuesta_decoded = json_decode($respuesta, true);

        if ($respuesta_decoded === null) {
            $errorMsg = "Respuesta de consulta de estado de baja de CPE ID {$cpe_id} no es JSON válido.";
            if ($isFactura || $isBoleta) {
                Log::channel('facturacion')->error($errorMsg);
            }
            if ($isBoleta) {
                Log::channel('boletas')->error($errorMsg);
            }
            throw new Exception("Respuesta inválida al consultar estado de baja.");
        }

        $successMsg = "Consulta de estado de baja de CPE ID {$cpe_id} completada. Respuesta: " . json_encode($respuesta_decoded);
        if ($isFactura || $isBoleta) {
            Log::channel('facturacion')->info($successMsg);
        }
        if ($isBoleta) {
            Log::channel('boletas')->info($successMsg);
        }

        return $respuesta_decoded;
    }
}