<?php

namespace App\Console\Commands;

use App\Models\Facturacion\Cpe;
use App\Services\CpeServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActualizarEstadoComprobantesSunat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:actualizar-estado-comprobantes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar estado de boletas y facturas en SUNAT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de estados de comprobantes en SUNAT...');
        
        $resultados = $this->actualizarPendientesAyer();
        
        $this->info("Actualizados: {$resultados['actualizados']}, Errores: {$resultados['errores']}");
        $this->info('Actualización completada.');
        return 0;
    }

    /**
     * Actualizar CPEs pendientes del día de ayer
     */
    private function actualizarPendientesAyer()
    {
        // $ayer = \Carbon\Carbon::create(2025, 11, 18)->startOfDay();
        // $hoy = \Carbon\Carbon::create(2025, 11, 18)->endOfDay();

        $ayer = now()->subDay()->startOfDay();
        $hoy = now()->subDay()->endOfDay();

        // Obtener CPEs pendientes de ayer
        $cpes = Cpe::whereBetween('created_at', [$ayer, $hoy])
                   ->where('aceptada_por_sunat', 0)
                   ->with(['posOrder.tienda', 'posOrder.user'])
                   ->get();
        
        $cpeServices = new CpeServices();
        $actualizados = 0;
        $errores = 0;

        $this->info("Encontrados {$cpes->count()} CPEs pendientes de ayer.");

        foreach ($cpes as $cpe) {
            try {
                $estado = $cpeServices->consultarEstadoCpe($cpe->id);
                
                if ($estado && isset($estado['aceptada_por_sunat'])) {
                    $cpe->aceptada_por_sunat = $estado['aceptada_por_sunat'];
                    $cpe->sunat_description = $estado['sunat_description'] ?? '';
                    $cpe->save();
                    $actualizados++;
                    $message = "CPE {$cpe->id}: {$cpe->serie}-{$cpe->numero} actualizado";
                    $this->line("✓ $message");
                    Log::channel('sunat')->info($message);
                }
            } catch (\Exception $e) {
                $errores++;
                $error_message = "Error actualizando CPE {$cpe->id}: " . $e->getMessage();
                $this->error("✗ $error_message");
                Log::channel('sunat')->error($error_message);
            }
        }

        return [
            'actualizados' => $actualizados,
            'errores' => $errores
        ];
    }
}
