<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\PosOrder;
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
        
        $data = $this->generator->generate($order);

        return response($data)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="ticket-'.$id.'.bin"');
    }
}
