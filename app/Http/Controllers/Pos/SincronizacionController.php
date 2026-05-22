<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\PosOrder;
use App\Models\Pos\PosOrderSync;
use App\Enums\SyncStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SincronizacionController extends Controller
{
    /**
     * Muestra la vista principal de sincronizaciones con el listado de fallos.
     */
    public function index()
    {
        // Obtener sincronizaciones fallidas paginadas
        $failedSyncs = PosOrderSync::with(['posOrder.tienda', 'posOrder.user', 'posOrder.cliente'])
            ->where('status', SyncStatus::FAILED)
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        // Estadísticas generales de la base de datos
        $totalOrders = PosOrder::count();
        $totalSuccess = PosOrderSync::where('status', SyncStatus::SUCCESS)->count();
        $totalFailed = PosOrderSync::where('status', SyncStatus::FAILED)->count();
        $totalPending = $totalOrders - ($totalSuccess + $totalFailed);

        return view('modules.ventas.sincronizaciones.index', compact(
            'failedSyncs',
            'totalOrders',
            'totalSuccess',
            'totalFailed',
            'totalPending'
        ));
    }

    /**
     * Obtiene estadísticas y el listado de órdenes en un rango de fechas.
     */
    public function getStatsByDateRange(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fin = Carbon::parse($request->fecha_fin)->endOfDay();

        // Órdenes en el rango
        $ordersInRange = PosOrder::with(['tienda', 'user', 'cliente'])
            ->whereBetween('order_date', [$inicio, $fin])
            ->get();

        $orderIds = $ordersInRange->pluck('id');

        // Estados de sincronización en el rango
        $syncs = PosOrderSync::whereIn('pos_order_id', $orderIds)->get()->keyBy('pos_order_id');

        $stats = [
            'total' => $ordersInRange->count(),
            'success' => 0,
            'failed' => 0,
            'pending' => 0,
        ];

        $ordersList = [];

        foreach ($ordersInRange as $order) {
            $sync = $syncs->get($order->id);
            $statusStr = 'pending';
            $attempts = 0;
            $lastError = null;

            if ($sync) {
                if ($sync->status === SyncStatus::SUCCESS) {
                    $stats['success']++;
                    $statusStr = 'success';
                } else {
                    $stats['failed']++;
                    $statusStr = 'failed';
                    $attempts = $sync->attempts;
                    $lastError = $sync->error_message;
                }
            } else {
                $stats['pending']++;
            }

            $ordersList[] = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'tipo_comprobante' => $order->tipo_comprobante,
                'serie' => $order->serie,
                'order_date' => $order->order_date,
                'total_amount' => $order->total_amount,
                'cliente' => $order->cliente->nombre ?? 'Varios',
                'tienda' => $order->tienda->nombre ?? 'N/A',
                'status' => $statusStr,
                'attempts' => $attempts,
                'last_error' => $lastError,
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'orders' => $ordersList,
        ]);
    }

    /**
     * Sincroniza todas las órdenes pendientes en un rango de fechas.
     */
    public function syncDateRange(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fin = Carbon::parse($request->fecha_fin)->endOfDay();

        // Obtener órdenes en el rango
        $orders = PosOrder::whereBetween('order_date', [$inicio, $fin])->get();

        $processed = 0;
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        foreach ($orders as $order) {
            // Verificar si ya está sincronizada con éxito
            $sync = PosOrderSync::where('pos_order_id', $order->id)->first();
            if ($sync && $sync->status === SyncStatus::SUCCESS) {
                $skippedCount++;
                continue;
            }

            $result = $this->syncOrder($order->id);
            $processed++;

            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Proceso terminado. Sincronizadas: {$successCount}, Fallidas: {$failedCount}, Omitidas: {$skippedCount}",
            'stats' => [
                'processed' => $processed,
                'success' => $successCount,
                'failed' => $failedCount,
                'skipped' => $skippedCount,
            ]
        ]);
    }

    /**
     * Sincroniza una orden específica.
     */
    public function syncSingle($orderId)
    {
        $result = $this->syncOrder($orderId);

        return response()->json($result);
    }

    /**
     * Reintenta una sincronización fallida específica.
     */
    public function retryFailedSync($id)
    {
        $sync = PosOrderSync::findOrFail($id);
        $result = $this->syncOrder($sync->pos_order_id);

        return response()->json($result);
    }

    /**
     * Reintenta todas las sincronizaciones fallidas pendientes.
     */
    public function retryAllFailed()
    {
        $failedSyncs = PosOrderSync::where('status', SyncStatus::FAILED)->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($failedSyncs as $sync) {
            $result = $this->syncOrder($sync->pos_order_id);
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Reintento masivo completado. Éxitos: {$successCount}, Fallas persistentes: {$failedCount}",
        ]);
    }

    /**
     * Método auxiliar centralizado para sincronizar una orden local con la nube.
     */
    protected function syncOrder($orderId)
    {
        Log::channel('sincronizaciones')->info("Iniciando sincronización manual/servidor para orden ID: {$orderId}");
        try {
            $order = PosOrder::findOrFail($orderId);
            
            // Resolver el controlador de órdenes para obtener el payload exacto
            $posOrderController = app(PosOrderController::class);
            $payload = $posOrderController->getSyncOrderData($orderId);

            // Realizar la petición HTTP POST a la nube
            $response = Http::connectTimeout(5)
                ->timeout(15)
                ->post('https://ahmodas.com/v2/api/sync/orders', $payload);

            if ($response->successful()) {
                // Sincronización exitosa
                PosOrderSync::updateOrCreate(
                    ['pos_order_id' => $orderId],
                    [
                        'payload' => $payload,
                        'status' => SyncStatus::SUCCESS,
                        'error_message' => null,
                        'error_details' => null,
                    ]
                );

                Log::channel('sincronizaciones')->info("Orden ID: {$orderId} sincronizada con éxito en la nube.");
                return ['success' => true, 'message' => "Orden {$orderId} sincronizada con éxito."];
            } else {
                // Error de respuesta HTTP (ej: 400, 500, etc.)
                $errorMessage = "Error HTTP {$response->status()}: " . ($response->json('message') ?? $response->body());
                $this->registerFailedSync($orderId, $payload, $errorMessage, [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);

                Log::channel('sincronizaciones')->error("Error en sincronización manual de orden ID: {$orderId}. Detalle: {$errorMessage}");
                return ['success' => false, 'message' => $errorMessage];
            }
        } catch (\Throwable $e) {
            // Error de conexión o excepción del sistema
            $errorMessage = "Error de conexión: " . $e->getMessage();
            $this->registerFailedSync($orderId, $payload ?? [], $errorMessage, [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            Log::channel('sincronizaciones')->critical("Excepción/Conexión fallida en sincronización de orden ID: {$orderId}. Mensaje: {$errorMessage}");
            return ['success' => false, 'message' => $errorMessage];
        }
    }

    /**
     * Registra el resultado del intento de sincronización realizado en el frontend.
     */
    public function markSyncStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:success,failed',
            'error_message' => 'nullable|string',
            'error_details' => 'nullable',
        ]);

        Log::channel('sincronizaciones')->info("Notificación desde caja recibida para orden ID: {$orderId}. Estado reportado: {$request->status}");

        try {
            $order = PosOrder::findOrFail($orderId);
            
            // Resolver el controlador de órdenes para obtener el payload exacto
            $posOrderController = app(PosOrderController::class);
            $payload = $posOrderController->getSyncOrderData($orderId);

            if ($request->status === 'success') {
                PosOrderSync::updateOrCreate(
                    ['pos_order_id' => $orderId],
                    [
                        'payload' => $payload,
                        'status' => SyncStatus::SUCCESS,
                        'error_message' => null,
                        'error_details' => null,
                    ]
                );
                Log::channel('sincronizaciones')->info("Notificación de éxito procesada localmente para orden ID: {$orderId}");
            } else {
                $errorMessage = $request->input('error_message') ?? 'Fallo al sincronizar desde el navegador';
                $errorDetails = $request->input('error_details');
                
                $this->registerFailedSync($orderId, $payload, $errorMessage, $errorDetails);
                Log::channel('sincronizaciones')->warning("Notificación de fallo procesada localmente para orden ID: {$orderId}. Error: {$errorMessage}");
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::channel('sincronizaciones')->error("Excepción en markSyncStatus para orden {$orderId}: " . $e->getMessage());
            Log::error("Error en markSyncStatus para orden {$orderId}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Registra o actualiza un fallo de sincronización en la base de datos.
     */
    protected function registerFailedSync($orderId, $payload, $message, $details = null)
    {
        $sync = PosOrderSync::where('pos_order_id', $orderId)->first();

        if ($sync) {
            $sync->update([
                'payload' => $payload,
                'status' => SyncStatus::FAILED,
                'error_message' => $message,
                'error_details' => $details,
                'attempts' => $sync->attempts + 1,
            ]);
        } else {
            PosOrderSync::create([
                'pos_order_id' => $orderId,
                'payload' => $payload,
                'status' => SyncStatus::FAILED,
                'error_message' => $message,
                'error_details' => $details,
                'attempts' => 1,
            ]);
        }
    }
}
