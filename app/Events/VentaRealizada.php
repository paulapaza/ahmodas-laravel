<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // se dispara al instante sin usar cola
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VentaRealizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $venta, public string $message = 'Venta realizada')
    {
        // Puedes inicializar propiedades adicionales si es necesario
    }
   

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            //new PrivateChannel('channel-name'),
            new Channel('ventas'),
        ];
    }
    public function broadcastAs(): string
    {
        return 'venta.realizada';
    }
    public function broadcastWith(): array
    {
        return [
            'venta' => $this->venta,
            'message' => $this->message,
        ];
    }
}
