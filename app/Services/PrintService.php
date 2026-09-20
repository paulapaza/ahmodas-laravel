<?php

namespace App\Services;


use App\Models\Playa\Parqueo;
use App\Models\Pos\PosOrder;
use Exception;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector; // Para Windows
use Illuminate\Http\JsonResponse;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Illuminate\Support\Facades\Auth;


class PrintService
{
    protected $printer;

    public function __construct()
    {

        $user = Auth::user();


        if (!$user) {
            throw new Exception("Usuario no autenticado.");
        }

        if (empty($user->print_type)) {
            throw new Exception("Tipo de impresora no configurado.");
        }

        switch ($user->print_type) {
            case 'red':
                // Verificar si la IP de la impresora está configurada
                if (empty($user->printer_ip)) {
                    throw new Exception("IP o puerto de impresora de red no configurados.");
                }
                try {
                    $ipParts = explode(':', $user->printer_ip);
                    $ip = $ipParts[0];
                    $port = isset($ipParts[1]) ? (int)$ipParts[1] : 9100;
                    
                    // IMPORTANTE: Timeout de 5 segundos para que no congele el sistema si está apagada
                    $connector = new NetworkPrintConnector($ip, $port, 5);
                } catch (Exception $e) {
                    throw new Exception("No se pudo conectar a la impresora de red: " . $e->getMessage());
                }

                break;

            case 'local':
                if (empty($user->printer_name)) {
                    throw new Exception("Nombre de impresora local no configurado.");
                }
                $connector = new WindowsPrintConnector($user->printer_name);
                break;

            default:
                throw new Exception("Tipo de impresora no soportado: ");
        }

        $this->printer = new Printer($connector);
    }
    /**
     * Imprime un ticket de entrada para un parqueo.
     * @param Parqueo $parqueo Objeto Parqueo recién creado, con código QR y fecha de ingreso.
     * @return JsonResponse Respuesta JSON indicando éxito o error.
     * */

    public function imprimirTicket(PosOrder $pos_order)
    {

        try {
            // CONECTOR WINDOWS


            // O para impresora de red:
            // $connector = new NetworkPrintConnector("192.168.0.100", 9100);

            $printer = $this->printer;
            $printer->initialize(); // Limpia memoria de la impresora para corte exacto
            $this->imprimirCabecera($printer, $pos_order);


            $printer->setFont(1);
            $printer->text("--------------------------------------------------------\n");
            $printer->setEmphasis(true);
            $printer->text("cant              Descripción                  subtotal\n");
            $printer->setEmphasis(false);
            $printer->text("--------------------------------------------------------\n");
            

            $widthDescription = 30;
            $printer->setFont(0);
       
            foreach ($pos_order->orderLines as $line) {
                $lineasDescripcion = $this->dividirEnLineas($line->producto->nombre, $widthDescription);

                // Imprimir la primera línea con todos los datos
                $printer->text(sprintf(
                    "%-3s %-30s %8s\n",
                    $line->quantity,
                    $lineasDescripcion[0],
                    number_format($line->subtotal, 2)
                ));

                // Imprimir las líneas restantes de la descripción
                for ($i = 1; $i < count($lineasDescripcion); $i++) {
                    $printer->text(sprintf(
                        "%-3s %-30s %8s\n",
                        "",  // Columna vacía para el stand
                        $lineasDescripcion[$i],
                        ""   // Columna vacía para el importe
                    ));
                }
            }
            $printer->setFont(1);
            $printer->text("--------------------------------------------------------\n");
            $printer->setFont(0);
            $printer->setEmphasis(true);
            //texto derecha
            $printer->text(sprintf("%-25s %-8s %8s\n", "", "Total:", number_format($pos_order->total_amount, 2)));
            $printer->setEmphasis(false);

            // Datos del vehículo
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->imprimirPie($printer, $pos_order);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }




    private function imprimirCabecera(Printer $printer, PosOrder $posOrder)
    {

        $logoPath = public_path('img/logo-maluz.png');


        if (file_exists($logoPath)) {
            $logo = EscposImage::load($logoPath, false);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->bitImage($logo);
        }

        $printer->feed(1);
        //$printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->text($posOrder->tienda->nombre . "\n");
        $printer->setTextSize(1, 1);
        $printer->text("Dirección: " . $posOrder->tienda->direccion . "\n");
        $printer->text("Telf.: " . $posOrder->tienda->telefono . "\n");
        $printer->setEmphasis(false);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $lineasNombre = $this->dividirEnLineas($posOrder->cliente->nombre, 30);

        $printer->text(sprintf("%-9s %-32s\n", "Cliente:", $lineasNombre[0], ""));
        for ($i = 1; $i < count($lineasNombre); $i++) {
            $printer->text(sprintf("%-9s %-32s\n", "", $lineasNombre[$i], ""));
        }
        $printer->text(sprintf("%-9s %-30s\n", "Nro Doc:", $posOrder->serie . '-' . $posOrder->order_number, ""));
        $printer->text(sprintf("%-9s %-30s\n", "Fecha:", $posOrder->order_date));
    }


    private function imprimirPie(Printer $printer, PosOrder $posOrder)
    {
        // Pie de página
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->feed(1);
        $printer->text($posOrder->tienda->ticket_nota ?? "Gracias por su compra\n");
        $printer->feed(2); // Avance extra para superar el borde de corte
        $printer->pulse(); // abre gabeta
        $printer->cut();
        $printer->close();
    }

    private function dividirEnLineas($texto, $ancho)
    {
        $lineas = [];
        while (strlen($texto) > $ancho) {
            $corte = strrpos(substr($texto, 0, $ancho), ' ');
            if ($corte === false) {
                $corte = $ancho;
            }
            $lineas[] = substr($texto, 0, $corte);
            $texto = substr($texto, $corte + 1);
        }
        $lineas[] = $texto;
        return $lineas;
    }

    public function imprimirComprobanteOficial(PosOrder $pos_order, $cpe_response = null)
    {
        try {
            $printer = $this->printer;
            $printer->initialize();
            
            // CABECERA OFICIAL
            $this->imprimirCabeceraOficial($printer, $pos_order);

            $printer->setFont(1);
            $printer->text("--------------------------------------------------------\n");
            $printer->setEmphasis(true);
            $printer->text("cant              Descripción                  subtotal\n");
            $printer->setEmphasis(false);
            $printer->text("--------------------------------------------------------\n");
            
            $widthDescription = 30;
            $printer->setFont(0);
       
            $opGravada = 0;
            $igv = 0;

            foreach ($pos_order->orderLines as $line) {
                // Cálculo simple de IGV asumiendo que los precios ya lo incluyen (18%)
                $subtotalLinea = $line->subtotal / 1.18;
                $opGravada += $subtotalLinea;
                $igv += ($line->subtotal - $subtotalLinea);

                $lineasDescripcion = $this->dividirEnLineas($line->producto->nombre, $widthDescription);

                $printer->text(sprintf(
                    "%-3s %-30s %8s\n",
                    $line->quantity,
                    $lineasDescripcion[0],
                    number_format($line->subtotal, 2)
                ));

                for ($i = 1; $i < count($lineasDescripcion); $i++) {
                    $printer->text(sprintf(
                        "%-3s %-30s %8s\n",
                        "",
                        $lineasDescripcion[$i],
                        ""
                    ));
                }
            }
            $printer->setFont(1);
            $printer->text("--------------------------------------------------------\n");
            $printer->setFont(0);
            
            // TOTALES DESGLOSADOS
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text(sprintf("Op. Gravada: S/ %8s\n", number_format($opGravada, 2)));
            $printer->text(sprintf("IGV (18%%): S/ %8s\n", number_format($igv, 2)));
            $printer->setEmphasis(true);
            $printer->text(sprintf("Total: S/ %8s\n", number_format($pos_order->total_amount, 2)));
            $printer->setEmphasis(false);
            $printer->text("\n");

            // PIE OFICIAL (QR, Hash, Text)
            $this->imprimirPieOficial($printer, $pos_order, $cpe_response);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    private function imprimirCabeceraOficial(Printer $printer, PosOrder $posOrder)
    {
        $logoPath = public_path('img/logo-maluz.png');
        if (file_exists($logoPath)) {
            $logo = EscposImage::load($logoPath, false);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->bitImage($logo);
        }

        $printer->feed(1);
        $printer->setEmphasis(true);
        $printer->text($posOrder->tienda->nombre . "\n");
        $printer->setTextSize(1, 1);
        
        // RUC de la tienda, asumiendo que existe el campo ruc. Si no, usa un genérico o el de la empresa.
        $rucTienda = $posOrder->tienda->ruc ?? "20000000000";
        $printer->text("RUC: " . $rucTienda . "\n");
        $printer->text("Dirección: " . $posOrder->tienda->direccion . "\n");
        $printer->text("Telf.: " . $posOrder->tienda->telefono . "\n");
        
        $printer->feed(1);
        
        $tipoStr = "TICKET DE VENTA";
        if ($posOrder->tipo_comprobante == '03') $tipoStr = "BOLETA DE VENTA ELECTRÓNICA";
        if ($posOrder->tipo_comprobante == '01') $tipoStr = "FACTURA ELECTRÓNICA";

        $printer->text($tipoStr . "\n");
        
        // Formato Serie-Número oficial (ej. B001-000001)
        $serieComprobante = $posOrder->serie . '-' . str_pad($posOrder->order_number, 8, '0', STR_PAD_LEFT);
        $printer->text($serieComprobante . "\n");
        
        $printer->setEmphasis(false);
        $printer->feed(1);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $lineasNombre = $this->dividirEnLineas($posOrder->cliente->nombre, 30);

        $printer->text(sprintf("%-9s %-32s\n", "Cliente:", $lineasNombre[0], ""));
        for ($i = 1; $i < count($lineasNombre); $i++) {
            $printer->text(sprintf("%-9s %-32s\n", "", $lineasNombre[$i], ""));
        }
        
        $numDoc = $posOrder->cliente->numero_documento ?? "-";
        $printer->text(sprintf("%-9s %-30s\n", "Doc/RUC:", $numDoc, ""));
        $printer->text(sprintf("%-9s %-30s\n", "Fecha:", $posOrder->order_date));
    }

    private function imprimirPieOficial(Printer $printer, PosOrder $posOrder, $cpe_response)
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->feed(1);

        $tipoStr = "Boleta";
        if ($posOrder->tipo_comprobante == '01') $tipoStr = "Factura";
        
        if (in_array($posOrder->tipo_comprobante, ['03', '01'])) {
            $printer->text("Representación impresa de la $tipoStr Electrónica\n");
            $printer->text("Consulte su comprobante en www.nubefact.com\n");
            
            if ($cpe_response && isset($cpe_response['codigo_hash'])) {
                $printer->text("Hash: " . $cpe_response['codigo_hash'] . "\n\n");
            }
            
            if ($cpe_response && isset($cpe_response['cadena_para_codigo_qr'])) {
                // Tamaño del módulo QR: 6 es grande y fácil de escanear
                $printer->qrCode($cpe_response['cadena_para_codigo_qr'], Printer::QR_ECLEVEL_L, 6);
                $printer->feed(1);
            }
        }

        $printer->text($posOrder->tienda->ticket_nota ?? "Gracias por su compra\n");
        $printer->feed(2); 
        $printer->pulse(); // Abre la gaveta de dinero
        $printer->cut();   // Corta el papel
        $printer->close(); // Cierra la conexión
    }
}
