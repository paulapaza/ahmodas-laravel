<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pos\PosDevolucion;
use App\Models\Pos\PosDevolucionDetalle;
use App\Services\PosServices;
use Illuminate\Support\Facades\Log;

class DevolucionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = PosDevolucion::with(['user:id,name', 'tienda:id,nombre,alias', 'detalles.producto:id,nombre'])
                ->orderBy('created_at', 'desc');
                
            // Si es cajero y NO es administrador, solo ver sus propias devoluciones
            if (auth()->user()->hasRole('cajero') && !auth()->user()->hasAnyRole(['Administrador', 'Super'])) {
                $query->where('user_id', auth()->id());
            }

            $devoluciones = $query->get();

            $devoluciones->transform(function($row) {
                $devueltos = [];
                $nuevos = [];
                foreach($row->detalles as $d) {
                    $nombre = $d->producto ? $d->producto->nombre : 'Prod. Eliminado';
                    $subtotal = number_format($d->subtotal, 2);
                    $texto = "{$d->cantidad}x {$nombre} <small class='text-muted'>(S/ {$subtotal})</small>";
                    if ($d->tipo_item === 'devuelto') {
                        $devueltos[] = "<span class='text-danger'>← {$texto}</span>";
                    } else {
                        $nuevos[] = "<span class='text-success'>→ {$texto}</span>";
                    }
                }
                
                $html = '';
                if (count($devueltos) > 0) {
                    $html .= implode('<br>', $devueltos);
                }
                if (count($nuevos) > 0) {
                    if ($html !== '') $html .= '<hr style="margin: 2px 0;">';
                    $html .= implode('<br>', $nuevos);
                }
                $row->resumen_productos = $html;
                return $row;
            });

            return response()->json($devoluciones);
        }

        return view('modules.ventas.devoluciones.index');
    }

    public function show($id)
    {
        $devolucion = PosDevolucion::with(['user:id,name', 'tienda:id,nombre', 'detalles.producto:id,nombre,codigo_barras'])
            ->findOrFail($id);
        
        return response()->json($devolucion);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tienda_id' => 'required|exists:tiendas,id',
            'productos_devueltos' => 'required|array',
            'productos_devueltos.*.id' => 'required|exists:productos,id',
            'productos_devueltos.*.cantidad' => 'required|integer|min:1',
            'productos_devueltos.*.precio_unitario' => 'required|numeric|min:0',
            'productos_nuevos' => 'nullable|array',
            'metodo_pago' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $productosDevueltos = $request->productos_devueltos;
            $productosNuevos = $request->productos_nuevos ?? [];

            $montoDevolucion = collect($productosDevueltos)->sum(function($p) {
                return $p['cantidad'] * $p['precio_unitario'];
            });

            $montoNuevo = collect($productosNuevos)->sum(function($p) {
                return $p['cantidad'] * $p['precio_unitario'];
            });

            $montoDiferencia = $montoNuevo - $montoDevolucion;
            $tipoMovimiento = empty($productosNuevos) ? 'devolucion_dinero' : 'cambio';

            $devolucion = PosDevolucion::create([
                'tienda_id' => $request->tienda_id,
                'user_id' => $request->user_id,
                'tipo_movimiento' => $tipoMovimiento,
                'monto_devolucion' => $montoDevolucion,
                'monto_nuevo' => $montoNuevo,
                'monto_diferencia' => $montoDiferencia,
                'metodo_pago' => $request->metodo_pago,
                'motivo' => $request->motivo,
            ]);

            $posServices = new PosServices();

            // Procesar devueltos (aumenta stock)
            foreach ($productosDevueltos as $p) {
                $subtotal = $p['cantidad'] * $p['precio_unitario'];
                PosDevolucionDetalle::create([
                    'pos_devolucion_id' => $devolucion->id,
                    'producto_id' => $p['id'],
                    'tipo_item' => 'devuelto',
                    'cantidad' => $p['cantidad'],
                    'precio_unitario' => $p['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
                
                // Actualizar stock (ingreso)
                $posServices->updateStockProductoTienda(
                    $p['id'], 
                    $request->tienda_id, 
                    'anulacion', // usamos 'anulacion' o algo equivalente que sume stock
                    $p['cantidad']
                );
            }

            // Procesar nuevos (disminuye stock)
            if (!empty($productosNuevos)) {
                $cantidadesNuevos = [];
                foreach ($productosNuevos as $p) {
                    $subtotal = $p['cantidad'] * $p['precio_unitario'];
                    PosDevolucionDetalle::create([
                        'pos_devolucion_id' => $devolucion->id,
                        'producto_id' => $p['id'],
                        'tipo_item' => 'nuevo',
                        'cantidad' => $p['cantidad'],
                        'precio_unitario' => $p['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);
                    $cantidadesNuevos[$p['id']] = $p['cantidad'];
                }
                
                // Actualizar stock (salida)
                $posServices->actualizarStockProductos($request->tienda_id, $cantidadesNuevos, 'venta'); // usamos 'venta' porque es una salida
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Devolución registrada correctamente.',
                'data' => $devolucion
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en DevolucionController: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }
}
