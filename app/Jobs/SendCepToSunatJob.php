<?php

namespace App\Jobs;

use App\Models\Cliente;
use App\Models\Facturacion\CpeSerie;
use App\Models\Pos\PosOrder;
use App\Services\CpeServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCepToSunatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $posOrder;
    protected $cpeSerie;
    protected $cliente;
    protected $tipoVenta;

    /**
     * Create a new job instance.
     */
    public function __construct(PosOrder $posOrder, CpeSerie $cpeSerie, Cliente $cliente, string $tipoVenta = 'local')
    {
        $this->posOrder = $posOrder;
        $this->cpeSerie = $cpeSerie;
        $this->cliente = $cliente;
        $this->tipoVenta = $tipoVenta;
    }

    /**
     * Execute the job.
     */
    public function handle(CpeServices $cpeServices): void
    {
        try {
            // Actualizamos la orden para verificar su estado actual en la base de datos
            $this->posOrder->refresh();

            // Si la orden fue anulada durante la espera, cancelamos el envío
            if ($this->posOrder->estado === 'anulado') {
                Log::info("Envío cancelado: La orden {$this->posOrder->id} fue anulada previamente.");
                return;
            }

            // Aseguramos que el correlativo sea el que se asignó en el momento de la venta
            // ya que el registro en la DB de cpe_series puede haber avanzado en la hora de espera.
            $this->cpeSerie->correlativo = $this->posOrder->order_number;

            Log::info("Enviando CPE a SUNAT desde Job. Orden: {$this->posOrder->id}, Serie: {$this->cpeSerie->serie}, Numero: {$this->cpeSerie->correlativo}");
            
            // $api_response = $cpeServices->SendCep(
            //     $this->cpeSerie, 
            //     $this->cliente, 
            //     $this->posOrder, 
            //     null, 
            //     null, 
            //     $this->tipoVenta
            // );

            Log::info("Respuesta de SUNAT para Orden {$this->posOrder->id}: " /*. json_encode($api_response)*/);

        } catch (\Throwable $e) {
            Log::error("Fallo envío CPE en Job para Orden {$this->posOrder->id}: " . $e->getMessage());
            // Re-lanzamos para que la cola reintente según la configuración
            throw $e;
        }
    }
}
