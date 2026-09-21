<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\PosOrder;
use App\Models\Facturacion\Cpe;
use App\Services\Print\TicketGenerator;
use Illuminate\Http\Response;

class PrintController extends Controller
{
    protected $generator;

    public function __construct(TicketGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Retorna el contenido binario ESC/POS de una orden.
     *
     * @param int $id
     * @return Response
     */
    public function getOrderTicket($id)
    {
        $order = PosOrder::with(['tienda', 'cliente', 'orderLines.producto'])->findOrFail($id);
        
        $cpe = Cpe::where('pos_order_id', $order->id)->first();

        // Si es Factura (01) o Boleta (03) y tiene datos de SUNAT, usamos el nuevo formato oficial
        if (in_array($order->tipo_comprobante, ['01', '03']) && $cpe) {
            $cpe_response = [
                'codigo_hash' => $cpe->codigo_hash,
                'cadena_para_codigo_qr' => $cpe->cadena_para_codigo_qr
            ];
            $data = $this->generator->generateOficial($order, $cpe_response);
        } else {
            // Para Tickets (12) o si no hay CPE, usamos el formato clásico intacto
            $data = $this->generator->generate($order);
        }

        return response($data)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="ticket-'.$id.'.bin"');
    }
}
