<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Traslado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MovimientoController extends Controller
{
    public function indexTransacciones()
    {
        $user = auth()->user();
        $tienda_id_usuario = $user->tienda_id;
        $es_admin = $user->hasAnyRole(['Administrador', 'Super']);
        $es_almacen = $user->hasRole('almacen');

        return view('modules.inventario.transacciones', compact('tienda_id_usuario', 'es_admin', 'es_almacen'));
    }

    public function indexKardex()
    {
        return view('modules.inventario.kardex');
    }

    public function storeTransaccion(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:ingreso,salida,transferencia',
            'producto_id' => 'required|exists:productos,id',
            'tienda_origen_id' => 'required_if:tipo,salida,transferencia',
            'tienda_destino_id' => 'required_if:tipo,ingreso,transferencia',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|in:compra,cambio,devolucion',
            'comentario' => 'nullable|string'
        ]);

        $tipo = $request->tipo;
        $productoId = $request->producto_id;
        $cantidad = $request->cantidad;
        $comentario = $request->comentario;

        try {
            // Validar stock si es salida o transferencia
            if ($tipo === 'salida' || $tipo === 'transferencia') {
                $stockActual = DB::table('producto_tienda')
                    ->where('producto_id', $productoId)
                    ->where('tienda_id', $request->tienda_origen_id)
                    ->value('stock') ?? 0;

                if ($stockActual < $cantidad) {
                    return response()->json(['error' => 'Stock insuficiente', 'message' => "Stock disponible: $stockActual unidades"], 422);
                }
            }

            DB::beginTransaction();

            if ($tipo === 'ingreso') {
                $this->ajustarStock($productoId, $request->tienda_destino_id, $cantidad, 3, $comentario, $request->motivo);
            } elseif ($tipo === 'salida') {
                $this->ajustarStock($productoId, $request->tienda_origen_id, -$cantidad, 1, $comentario);
            } elseif ($tipo === 'transferencia') {
                // Salida de origen
                $this->ajustarStock($productoId, $request->tienda_origen_id, -$cantidad, 4, "Transferencia a tienda " . $request->tienda_destino_id . ". " . $comentario);
                // Ingreso a destino
                $this->ajustarStock($productoId, $request->tienda_destino_id, $cantidad, 5, "Transferencia desde tienda " . $request->tienda_origen_id . ". " . $comentario);
            }

            DB::commit();
            return response()->json(['message' => 'Transacción registrada correctamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar la transacción', 'detail' => $e->getMessage()], 500);
        }
    }

    private function ajustarStock($productoId, $tiendaId, $variacion, $tipoMovimiento, $comentario, $motivo = null)
    {
        $stockActual = DB::table('producto_tienda')
            ->where('producto_id', $productoId)
            ->where('tienda_id', $tiendaId)
            ->value('stock') ?? 0;

        $nuevoStock = $stockActual + $variacion;

        // Actualizar stock
        DB::table('producto_tienda')->updateOrInsert(
            ['producto_id' => $productoId, 'tienda_id' => $tiendaId],
            ['stock' => $nuevoStock, 'updated_at' => now()]
        );

        // Registrar en historial (salida_productos actúa como tabla de movimientos)
        DB::table('salida_productos')->insert([
            'producto_id' => $productoId,
            'tienda_id' => $tiendaId,
            'stock_antes' => $stockActual,
            'stock_despues' => $nuevoStock,
            'cantidad_reducida' => abs($variacion),
            'tipo' => $tipoMovimiento,
            'motivo' => $motivo ?? ($tipoMovimiento == 3 ? 'compra' : null),
            'comentario' => $comentario,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function getKardexData(Request $request)
    {
        $query = DB::table('salida_productos as sp')
            ->join('productos as p', 'sp.producto_id', '=', 'p.id')
            ->join('tiendas as t', 'sp.tienda_id', '=', 't.id')
            ->select(
                'sp.id',
                'p.nombre as producto',
                'p.codigo_barras',
                't.nombre as tienda',
                'sp.stock_antes',
                'sp.cantidad_reducida',
                'sp.stock_despues',
                'sp.tipo',
                'sp.comentario',
                'sp.created_at'
            );

        if ($request->producto_id) {
            $query->where('sp.producto_id', $request->producto_id);
        }

        if ($request->tienda_id) {
            $query->where('sp.tienda_id', $request->tienda_id);
        }

        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereBetween('sp.created_at', [$request->fecha_inicio . ' 00:00:00', $request->fecha_fin . ' 23:59:59']);
        }

        return DataTables::of($query)
            ->editColumn('tipo', function ($row) {
                $tipos = [
                    1 => 'Salida Manual',
                    2 => 'Venta',
                    3 => 'Ingreso Manual',
                    4 => 'Transferencia (Salida)',
                    5 => 'Transferencia (Ingreso)'
                ];
                return $tipos[$row->tipo] ?? 'Desconocido';
            })
            ->make(true);
    }

    public function getStock($productoId, $tiendaId)
    {
        $stock = DB::table('producto_tienda')
            ->where('producto_id', $productoId)
            ->where('tienda_id', $tiendaId)
            ->value('stock') ?? 0;

        return response()->json(['stock' => $stock]);
    }

    public function storeTrasladoMasivo(Request $request)
    {
        $request->validate([
            'tienda_origen_id' => 'required|exists:tiendas,id',
            'tienda_destino_id' => 'required|exists:tiendas,id|different:tienda_origen_id',
            'comentario' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Generar código único para el traslado (T-000001)
            $ultimoTraslado = Traslado::latest()->first();
            $nuevoNumero = $ultimoTraslado ? (int) str_replace('T-', '', $ultimoTraslado->codigo) + 1 : 1;
            $codigo = 'T-' . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);

            $traslado = Traslado::create([
                'tienda_origen_id' => $request->tienda_origen_id,
                'tienda_destino_id' => $request->tienda_destino_id,
                'user_id' => Auth::id(),
                'codigo' => $codigo,
                'comentario' => $request->comentario
            ]);

            foreach ($request->items as $item) {
                $productoId = $item['producto_id'];
                $cantidad = $item['cantidad'];

                // 1. Validar stock en origen
                $stockActualOrigen = DB::table('producto_tienda')
                    ->where('producto_id', $productoId)
                    ->where('tienda_id', $request->tienda_origen_id)
                    ->value('stock') ?? 0;

                if ($stockActualOrigen < $cantidad) {
                    throw new \Exception("Stock insuficiente para el producto ID $productoId. Disponible: $stockActualOrigen");
                }

                // 2. Ejecutar ajuste de stock (Salida Origen)
                $this->ajustarStockConTraslado($productoId, $request->tienda_origen_id, -$cantidad, 4, "Traslado $codigo a tienda " . $request->tienda_destino_id, $traslado->id);

                // 3. Ejecutar ajuste de stock (Ingreso Destino)
                $this->ajustarStockConTraslado($productoId, $request->tienda_destino_id, $cantidad, 5, "Traslado $codigo desde tienda " . $request->tienda_origen_id, $traslado->id);
            }

            DB::commit();
            return response()->json([
                'message' => 'Traslado registrado correctamente',
                'traslado_id' => $traslado->id,
                'codigo' => $traslado->codigo
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar el traslado', 'detail' => $e->getMessage()], 500);
        }
    }

    private function ajustarStockConTraslado($productoId, $tiendaId, $variacion, $tipoMovimiento, $comentario, $trasladoId)
    {
        $stockActual = DB::table('producto_tienda')
            ->where('producto_id', $productoId)
            ->where('tienda_id', $tiendaId)
            ->value('stock') ?? 0;

        $nuevoStock = $stockActual + $variacion;

        DB::table('producto_tienda')->updateOrInsert(
            ['producto_id' => $productoId, 'tienda_id' => $tiendaId],
            ['stock' => $nuevoStock, 'updated_at' => now()]
        );

        DB::table('salida_productos')->insert([
            'producto_id' => $productoId,
            'tienda_id' => $tiendaId,
            'stock_antes' => $stockActual,
            'stock_despues' => $nuevoStock,
            'cantidad_reducida' => abs($variacion),
            'tipo' => $tipoMovimiento,
            'comentario' => $comentario,
            'traslado_id' => $trasladoId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function imprimirTraslado($id)
    {
        $traslado = Traslado::with(['tiendaOrigen', 'tiendaDestino', 'user', 'items.producto'])->findOrFail($id);
        return view('modules.inventario.traslados.imprimir', compact('traslado'));
    }
}
