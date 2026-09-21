<?php

namespace App\Services\Print;

use App\Models\Pos\PosOrder;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;

class TicketGenerator
{
    /**
     * Genera el contenido binario ESC/POS para una orden.
     *
     * @param PosOrder $pos_order
     * @return string
     */
    public function generate(PosOrder $pos_order): string
    {
        $connector = new DummyPrintConnector();
        $printer = new Printer($connector);

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
                    "",  // Columna vacía
                    $lineasDescripcion[$i],
                    ""   // Columna vacía
                ));
            }
        }

        $printer->setFont(1);
        $printer->text("--------------------------------------------------------\n");
        $printer->setFont(0);
        $printer->setEmphasis(true);
        $printer->text(sprintf("%-25s %-8s %8s\n", "", "Total:", number_format($pos_order->total_amount, 2)));
        $printer->setEmphasis(false);

        $printer->feed(1);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        
        $this->imprimirPie($printer, $pos_order);

        $data = $connector->getData();
        $printer->close();

        return $data;
    }

    private function imprimirCabecera(Printer $printer, PosOrder $posOrder)
    {
        $logoPath = public_path('img/logo-maluz.png');

        if (file_exists($logoPath)) {
            $logo = EscposImage::load($logoPath, false);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->bitImage($logo);
        }

        $printer->feed(2);
        $printer->setEmphasis(true);
        $printer->text($posOrder->tienda->nombre . "\n");
        $printer->setTextSize(1, 1);
        $printer->text("Dirección: " . $posOrder->tienda->direccion . "\n");
        $printer->text("Telf.: " . $posOrder->tienda->telefono . "\n");
        $printer->setEmphasis(false);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $lineasNombre = $this->dividirEnLineas($posOrder->cliente?->nombre ?? 'PUBLICO EN GENERAL', 30);

        $printer->text(sprintf("%-9s %-32s\n", "Cliente:", $lineasNombre[0]));
        for ($i = 1; $i < count($lineasNombre); $i++) {
            $printer->text(sprintf("%-9s %-32s\n", "", $lineasNombre[$i]));
        }
        $printer->text(sprintf("%-9s %-30s\n", "Nro Doc:", $posOrder->serie . '-' . $posOrder->order_number));
        $printer->text(sprintf("%-9s %-30s\n", "Fecha:", $posOrder->order_date));
    }

    private function imprimirPie(Printer $printer, PosOrder $posOrder)
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->feed(1);
        $printer->text($posOrder->tienda->ticket_nota ?? "Gracias por su compra\n");
        $printer->feed(2);
        $printer->pulse(); // abre gaveta
        $printer->cut();
    }

    public function generateOficial(PosOrder $pos_order, $cpe_response = null): string
    {
        $connector = new DummyPrintConnector();
        $printer = new Printer($connector);

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

        $data = $connector->getData();
        $printer->close();

        return $data;
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
        
        $rucTienda = $posOrder->tienda->ruc ?? "20000000000";
        $printer->text("RUC: " . $rucTienda . "\n");
        $printer->text("Dirección: " . $posOrder->tienda->direccion . "\n");
        $printer->text("Telf.: " . $posOrder->tienda->telefono . "\n");
        
        $printer->feed(1);
        
        $tipoStr = "TICKET DE VENTA";
        if ($posOrder->tipo_comprobante == '03') $tipoStr = "BOLETA DE VENTA ELECTRÓNICA";
        if ($posOrder->tipo_comprobante == '01') $tipoStr = "FACTURA ELECTRÓNICA";

        $printer->text($tipoStr . "\n");
        
        $serieComprobante = $posOrder->serie . '-' . str_pad($posOrder->order_number, 8, '0', STR_PAD_LEFT);
        $printer->text($serieComprobante . "\n");
        
        $printer->setEmphasis(false);
        $printer->feed(1);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $lineasNombre = $this->dividirEnLineas($posOrder->cliente?->nombre ?? 'PUBLICO EN GENERAL', 30);

        $printer->text(sprintf("%-9s %-32s\n", "Cliente:", $lineasNombre[0]));
        for ($i = 1; $i < count($lineasNombre); $i++) {
            $printer->text(sprintf("%-9s %-32s\n", "", $lineasNombre[$i]));
        }
        
        $numDoc = $posOrder->cliente?->numero_documento ?? "-";
        $printer->text(sprintf("%-9s %-30s\n", "Doc/RUC:", $numDoc));
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
                $printer->qrCode($cpe_response['cadena_para_codigo_qr'], Printer::QR_ECLEVEL_L, 6);
                $printer->feed(1);
            }
        }

        $printer->text($posOrder->tienda->ticket_nota ?? "Gracias por su compra\n");
        $printer->feed(2); 
        $printer->pulse(); // Abre gaveta
        $printer->cut();   // Corta el papel
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
}
