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

                    $connector = new NetworkPrintConnector($user->printer_ip, 9100);
                } catch (Exception $e) {

                    throw new Exception("No se pudo conectar a la impresora de red: ");
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

            $printer->feed(1);

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

        $printer->feed(2);
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
        $printer->feed(2);
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
}
