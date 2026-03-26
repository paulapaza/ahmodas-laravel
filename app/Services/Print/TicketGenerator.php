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
        $lineasNombre = $this->dividirEnLineas($posOrder->cliente->nombre, 30);

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
