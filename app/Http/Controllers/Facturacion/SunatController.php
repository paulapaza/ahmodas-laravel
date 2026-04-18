<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\Cpe;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SunatController extends Controller
{
    public function indexCpes()
    {
        $cpes = Cpe::with('posOrder.cliente', 'posOrder.tienda')
            ->orderBy('id', 'desc')
            ->paginate(50);
        
        return view('modules.facturacion.sunat.cpes', compact('cpes'));
    }

    public function indexJobs()
    {
        $jobs = DB::table('jobs')->get()->map(function($job) {
            $payload = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? 'N/A';
            
            // Intentar extraer el ID de la orden si es un SendCepToSunatJob
            $job->order_id = 'N/A';
            if (isset($payload['data']['command'])) {
                if (preg_match('/"id";i:(\d+);/', $payload['data']['command'], $matches)) {
                    $job->order_id = $matches[1];
                }
            }
            return $job;
        });

        return view('modules.facturacion.sunat.jobs', compact('jobs'));
    }

    public function indexFailedJobs()
    {
        $failedJobs = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->get()->map(function($job) {
            $payload = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? 'N/A';
            
            $job->order_id = 'N/A';
            if (isset($payload['data']['command'])) {
                if (preg_match('/"id";i:(\d+);/', $payload['data']['command'], $matches)) {
                    $job->order_id = $matches[1];
                }
            }
            return $job;
        });

        return view('modules.facturacion.sunat.failed_jobs', compact('failedJobs'));
    }

    public function sendNow($id, \App\Services\CpeServices $cpeServices)
    {
        $job = DB::table('jobs')->where('id', $id)->first();

        if (!$job) {
            return back()->with('error', 'El trabajo ya no existe en la cola (probablemente ya fue procesado).');
        }

        if ($job->reserved_at) {
            return back()->with('error', 'El trabajo está siendo procesado por el servidor en este momento.');
        }

        // Intentar eliminar para tomar control del Job y evitar que el worker lo procese
        $deleted = DB::table('jobs')->where('id', $id)->whereNull('reserved_at')->delete();

        if (!$deleted) {
            return back()->with('error', 'No se pudo obtener el control del trabajo. Es posible que el servidor lo haya tomado justo ahora.');
        }

        $payload = json_decode($job->payload, true);

        try {
            // Unserializar el objeto del Job
            $command = unserialize($payload['data']['command']);
            
            // Ejecutar el método handle del Job inyectando el servicio de SUNAT
            $command->handle($cpeServices);

            return back()->with('success', '¡Éxito! El comprobante ha sido enviado a SUNAT manualmente.');
            
        } catch (\Throwable $e) {
            // EN CASO DE ERROR: Lo movemos a failed_jobs para que el usuario no pierda el registro
            DB::table('failed_jobs')->insert([
                'uuid' => $payload['uuid'] ?? (string) \Illuminate\Support\Str::uuid(),
                'connection' => $job->connection ?? 'database',
                'queue' => $job->queue ?? 'default',
                'payload' => $job->payload,
                'exception' => (string) $e,
                'failed_at' => now(),
            ]);

            return back()->with('error', 'El envío falló. El trabajo se ha movido a la pestaña de "Envíos Fallidos" con el siguiente error: ' . $e->getMessage());
        }
    }

    public function actualizarEstadosPendientes(Request $request, \App\Services\CpeServices $cpeServices)
    {
        \Illuminate\Support\Facades\Log::channel('sunat')->info('Actualización manual de estados de CPEs iniciada desde interfaz web');
        
        $fechaInicio = $request->input('fecha_inicio') ? \Carbon\Carbon::parse($request->input('fecha_inicio'))->startOfDay() : now()->subDay()->startOfDay();
        $fechaFin = $request->input('fecha_fin') ? \Carbon\Carbon::parse($request->input('fecha_fin'))->endOfDay() : now()->subDay()->endOfDay();

        \Illuminate\Support\Facades\Log::channel('sunat')->info("Rango de fechas: {$fechaInicio->format('Y-m-d')} a {$fechaFin->format('Y-m-d')}");

        // Obtener CPEs pendientes en el rango de fechas
        $cpes = Cpe::whereBetween('created_at', [$fechaInicio, $fechaFin])
                   ->where('aceptada_por_sunat', 0)
                   ->with(['posOrder.tienda', 'posOrder.user'])
                   ->get();

        \Illuminate\Support\Facades\Log::channel('sunat')->info("Encontrados {$cpes->count()} CPEs pendientes");

        $actualizados = 0;
        $errores = 0;

        foreach ($cpes as $cpe) {
            try {
                $estado = $cpeServices->consultarEstadoCpe($cpe->id);
                
                if ($estado && isset($estado['aceptada_por_sunat'])) {
                    $cpe->aceptada_por_sunat = $estado['aceptada_por_sunat'];
                    $cpe->sunat_description = $estado['sunat_description'] ?? '';
                    $cpe->save();
                    $actualizados++;
                    $message = "CPE {$cpe->id}: {$cpe->serie}-{$cpe->numero} actualizado (manual)";
                    \Illuminate\Support\Facades\Log::channel('sunat')->info($message);
                }
            } catch (\Exception $e) {
                $errores++;
                $error_message = "Error actualizando CPE {$cpe->id}: " . $e->getMessage();
                \Illuminate\Support\Facades\Log::channel('sunat')->error($error_message);
            }
        }

        \Illuminate\Support\Facades\Log::channel('sunat')->info("Actualización manual completada. Actualizados: $actualizados, Errores: $errores");

        return back()->with('success', "Actualización completada. Actualizados: $actualizados, Errores: $errores");
    }
}
