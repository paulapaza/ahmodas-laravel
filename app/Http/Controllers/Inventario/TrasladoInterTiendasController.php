<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrasladoInterTiendasController extends Controller
{
    public function index()
    {
        return view('modules.inventario.traslados-inter-tiendas.gestionar');
    }

    public function historial()
    {
        return view('modules.inventario.traslados-inter-tiendas.historial');
    }

    private function getAlmacenId()
    {
        $id = DB::table('tiendas')->where('es_almacen', 1)->value('id');
        return $id;
    }

    public function getDataGestionApi(Request $request)
    {
        $almacenId = $this->getAlmacenId();
        $fechaFiltro = $request->input('fecha') ?: now()->toDateString();

        // Tiendas (excluyendo el almacén)
        $tiendas = DB::table('tiendas')
            ->where('id', '!=', $almacenId)
            ->where('estado', 1)
            ->select('id', 'nombre', 'alias')
            ->orderBy('nombre')
            ->get();

        // Traslados ya realizados hoy
        $trasladosHoy = DB::table('inter_tiendas_traslados as itt')
            ->join('productos as p', 'itt.producto_id', '=', 'p.id')
            ->where('itt.fecha', $fechaFiltro)
            ->select(
                'itt.id as traslado_id',
                'itt.producto_id as id',
                'p.nombre as producto_nombre',
                'p.alias as producto_alias',
                'p.codigo_barras as producto_codigo',
                'itt.tienda_origen_id',
                'itt.tienda_destino_id',
                'itt.cantidad',
                'itt.stock_origen_anterior',
                'itt.stock_origen_posterior',
                'itt.stock_destino_anterior',
                'itt.stock_destino_posterior',
                'itt.created_at as fecha'
            )
            ->orderByDesc('itt.id')
            ->get();

        return response()->json([
            'tiendas' => $tiendas,
            'confirmados' => $trasladosHoy,
            'almacen_id' => $almacenId
        ]);
    }

    public function getProductsByTienda(Request $request, $tiendaId)
    {
        $destinoId = $request->query('destino_id');

        // Productos con stock en la tienda seleccionada (Origen)
        $productos = DB::table('productos')
            ->join('producto_tienda', 'productos.id', '=', 'producto_tienda.producto_id')
            ->where('producto_tienda.tienda_id', $tiendaId)
            ->where('producto_tienda.stock', '>', 0)
            ->where('productos.estado', 1)
            ->select(
                'productos.id', 
                'productos.nombre', 
                'productos.alias', 
                'productos.codigo_barras as codigo', 
                'producto_tienda.stock as stock_tienda'
            )
            ->get();

        // Si se envió un destino, buscamos cuánto stock tiene ese destino de estos productos
        if ($destinoId) {
            $stocksDestino = DB::table('producto_tienda')
                ->where('tienda_id', $destinoId)
                ->whereIn('producto_id', $productos->pluck('id'))
                ->select('producto_id', 'stock')
                ->get()
                ->keyBy('producto_id');

            $productos->map(function ($p) use ($stocksDestino) {
                $p->stock_destino = $stocksDestino->get($p->id)->stock ?? 0;
                return $p;
            });
        }

        return response()->json($productos);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'tienda_origen_id' => 'required|integer',
                'tienda_destino_id' => 'required|integer|different:tienda_origen_id',
                'traslados' => 'required|array',
            ]);

            $traslados = $request->input('traslados');
            $origenId = $request->input('tienda_origen_id');
            $destinoId = $request->input('tienda_destino_id');
            
            $almacenId = $this->getAlmacenId();
            if ($origenId == $almacenId || $destinoId == $almacenId) {
                 throw new \Exception("No puede usar el almacén principal para esta operación.");
            }

            $hoy = now();
            $userId = $request->input('user_id') ?? auth()->id();

            DB::beginTransaction();

            foreach ($traslados as $t) {
                // Para evitar deadlocks, bloqueamos siempre primero el ID de tienda más pequeño
                $minTienda = min($origenId, $destinoId);
                $maxTienda = max($origenId, $destinoId);

                $stockMin = DB::table('producto_tienda')
                    ->where('producto_id', $t['producto_id'])
                    ->where('tienda_id', $minTienda)
                    ->lockForUpdate()
                    ->first();

                $stockMax = DB::table('producto_tienda')
                    ->where('producto_id', $t['producto_id'])
                    ->where('tienda_id', $maxTienda)
                    ->lockForUpdate()
                    ->first();

                $origenStock = $minTienda == $origenId ? $stockMin : $stockMax;
                $destinoStock = $minTienda == $destinoId ? $stockMin : $stockMax;

                if (!$origenStock || $origenStock->stock < $t['cantidad']) {
                    $productoNombre = DB::table('productos')->where('id', $t['producto_id'])->value('nombre');
                    throw new \Exception("Stock insuficiente en tienda origen para '{$productoNombre}'.");
                }

                $stockDestinoAnterior = $destinoStock ? $destinoStock->stock : 0;

                // 1. Descontar de Origen
                DB::table('producto_tienda')
                    ->where('id', $origenStock->id)
                    ->decrement('stock', $t['cantidad']);

                // 2. Aumentar en Destino
                if ($destinoStock) {
                    DB::table('producto_tienda')
                        ->where('id', $destinoStock->id)
                        ->increment('stock', $t['cantidad']);
                } else {
                    DB::table('producto_tienda')->insert([
                        'producto_id' => $t['producto_id'],
                        'tienda_id' => $destinoId,
                        'stock' => $t['cantidad'],
                        'created_at' => $hoy,
                        'updated_at' => $hoy,
                    ]);
                }

                // 3. Registrar Traslado en el historial
                DB::table('inter_tiendas_traslados')->insert([
                    'tienda_origen_id' => $origenId,
                    'tienda_destino_id' => $destinoId,
                    'producto_id' => $t['producto_id'],
                    'fecha' => $hoy->toDateString(),
                    
                    'stock_origen_anterior' => $origenStock->stock,
                    'stock_origen_posterior' => $origenStock->stock - $t['cantidad'],
                    
                    'stock_destino_anterior' => $stockDestinoAnterior,
                    'stock_destino_posterior' => $stockDestinoAnterior + $t['cantidad'],
                    
                    'cantidad' => $t['cantidad'],
                    
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $hoy,
                    'updated_at' => $hoy,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Traslados entre tiendas procesados correctamente.'
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHistorialGlobal(Request $request)
    {
        $tiendaOrigenId = $request->get('tienda_origen_id');
        $tiendaDestinoId = $request->get('tienda_destino_id');
        $fecha = $request->get('fecha');
        $search = $request->get('search');

        $query = DB::table('inter_tiendas_traslados as itt')
            ->join('productos as p', 'itt.producto_id', '=', 'p.id')
            ->join('tiendas as to', 'itt.tienda_origen_id', '=', 'to.id')
            ->join('tiendas as td', 'itt.tienda_destino_id', '=', 'td.id')
            ->leftJoin('users as u', 'itt.created_by', '=', 'u.id')
            ->select(
                'itt.id as traslado_id',
                'p.nombre as producto_nombre',
                'p.alias as producto_alias',
                'p.codigo_barras as codigo',
                'to.nombre as tienda_origen',
                'td.nombre as tienda_destino',
                'itt.cantidad',
                'itt.stock_origen_anterior',
                'itt.stock_origen_posterior',
                'itt.stock_destino_anterior',
                'itt.stock_destino_posterior',
                'itt.created_at',
                'u.name as user_name'
            );

        if ($tiendaOrigenId) $query->where('itt.tienda_origen_id', $tiendaOrigenId);
        if ($tiendaDestinoId) $query->where('itt.tienda_destino_id', $tiendaDestinoId);
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.nombre', 'like', "%{$search}%")
                  ->orWhere('p.alias', 'like', "%{$search}%")
                  ->orWhere('p.codigo_barras', 'like', "%{$search}%");
            });
        }
        
        if ($fecha) $query->where('itt.fecha', $fecha);

        $traslados = $query->orderByDesc('itt.id')->get()->map(function ($t) {
            $t->created_fmt = date('d/m/Y h:i A', strtotime($t->created_at));
            return $t;
        });

        return response()->json([
            'success' => true,
            'traslados' => $traslados
        ]);
    }
}
