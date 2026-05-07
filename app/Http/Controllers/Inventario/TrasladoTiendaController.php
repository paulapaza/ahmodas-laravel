<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Inventario\TrasladoTiendaStoreRequest;

class TrasladoTiendaController extends Controller
{
   public function index()
   {
      return view('modules.inventario.traslados-tiendas.gestionar');
   }

   public function historial()
   {
      return view('modules.inventario.traslados-tiendas.historial');
   }

   private function getAlmacenId()
   {
      $id = DB::table('tiendas')->where('es_almacen', 1)->value('id');

      if (!$id) {
         throw new \Exception("No se ha configurado ninguna tienda como Almacén Principal en el sistema.");
      }

      return $id;
   }

   public function getDataGestionApi(Request $request)
   {
      $almacenId = $this->getAlmacenId();
      $fechaFiltro = $request->input('fecha') ?: now()->toDateString();

      // Tiendas origen (excluyendo el almacén)
      $tiendas = DB::table('tiendas')
         ->where('id', '!=', $almacenId)
         ->where('estado', 1)
         ->select('id', 'nombre', 'alias')
         ->orderBy('nombre')
         ->get();

      // Traslados ya realizados hoy (opcional, para visualización similar a la otra sección)
      $trasladosHoy = DB::table('tiendas_traslados')
         ->join('productos', 'tiendas_traslados.producto_id', '=', 'productos.id')
         ->join('tiendas', 'tiendas_traslados.tienda_id', '=', 'tiendas.id')
         ->where('tiendas_traslados.fecha', $fechaFiltro)
         ->select(
            'tiendas_traslados.id as traslado_id',
            'tiendas_traslados.producto_id as id',
            'productos.nombre',
            'productos.alias',
            'productos.codigo_barras as codigo',
            'tiendas_traslados.tienda_id',
            'tiendas.nombre as tienda_nombre',
            'tiendas.alias as tienda_alias',
            'tiendas_traslados.cantidad',
            'tiendas_traslados.tienda_stock_anterior',
            'tiendas_traslados.almacen_stock_anterior',
            'tiendas_traslados.tienda_stock_posterior',
            'tiendas_traslados.almacen_stock_posterior',
            'tiendas_traslados.created_at as fecha'
         )
         ->orderByDesc('tiendas_traslados.id')
         ->get();

      return response()->json([
         'tiendas' => $tiendas,
         'confirmados' => $trasladosHoy,
         'almacen_id' => $almacenId
      ]);
   }

   public function getProductsByTienda(Request $request, $tiendaId)
   {
      $almacenId = $this->getAlmacenId();

      // Productos con stock en la tienda seleccionada
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

      // Obtener stock actual en almacén para estos productos
      $stocksAlmacen = DB::table('producto_tienda')
         ->where('tienda_id', $almacenId)
         ->whereIn('producto_id', $productos->pluck('id'))
         ->select('producto_id', 'stock')
         ->get()
         ->keyBy('producto_id');

      $productos->map(function ($p) use ($stocksAlmacen) {
         $p->stock_almacen = $stocksAlmacen->get($p->id)->stock ?? 0;
         return $p;
      });

      return response()->json($productos);
   }

   public function store(TrasladoTiendaStoreRequest $request)
   {
      try {
         $traslados = $request->input('traslados');
         $hoy = now();
         $almacenId = $this->getAlmacenId();
         $userId = $request->input('user_id') ?? auth()->id();

         DB::beginTransaction();

         foreach ($traslados as $t) {
            // 1. Bloqueo y obtención de stock en TIENDA (Origen)
            $tiendaStock = DB::table('producto_tienda')
               ->where('producto_id', $t['producto_id'])
               ->where('tienda_id', $t['tienda_id'])
               ->lockForUpdate()
               ->first();

            if (!$tiendaStock || $tiendaStock->stock < $t['cantidad']) {
               $productoNombre = DB::table('productos')->where('id', $t['producto_id'])->value('nombre');
               throw new \Exception("Stock insuficiente en tienda para '{$productoNombre}'.");
            }

            // 2. Bloqueo y obtención de stock en ALMACEN (Destino)
            $almacenStock = DB::table('producto_tienda')
               ->where('producto_id', $t['producto_id'])
               ->where('tienda_id', $almacenId)
               ->lockForUpdate()
               ->first();

            $stockAlmacenAnterior = $almacenStock ? $almacenStock->stock : 0;

            // 3. Ejecutar Movimiento
            // Descontar de Tienda
            DB::table('producto_tienda')
               ->where('id', $tiendaStock->id)
               ->decrement('stock', $t['cantidad']);

            // Aumentar en Almacén
            if ($almacenStock) {
               DB::table('producto_tienda')
                  ->where('id', $almacenStock->id)
                  ->increment('stock', $t['cantidad']);
            } else {
               DB::table('producto_tienda')->insert([
                  'producto_id' => $t['producto_id'],
                  'tienda_id' => $almacenId,
                  'stock' => $t['cantidad'],
                  'created_at' => $hoy,
                  'updated_at' => $hoy,
               ]);
            }

            // 4. Registrar Traslado
            $trasladoId = DB::table('tiendas_traslados')->insertGetId([
               'tienda_id' => $t['tienda_id'],
               'almacen_id' => $almacenId,
               'producto_id' => $t['producto_id'],
               'fecha' => $hoy->toDateString(),
               'tienda_stock_anterior' => $tiendaStock->stock,
               'almacen_stock_anterior' => $stockAlmacenAnterior,
               'tienda_stock_posterior' => $tiendaStock->stock - $t['cantidad'],
               'almacen_stock_posterior' => $stockAlmacenAnterior + $t['cantidad'],
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
            'message' => 'Traslados desde tiendas procesados correctamente.'
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
      $almacenId = $this->getAlmacenId();
      $tiendaId = $request->get('tienda_id');
      $fecha = $request->get('fecha');
      $search = $request->get('search');

      $query = DB::table('tiendas_traslados')
         ->join('productos', 'tiendas_traslados.producto_id', '=', 'productos.id')
         ->join('tiendas', 'tiendas_traslados.tienda_id', '=', 'tiendas.id')
         ->leftJoin('users', 'tiendas_traslados.created_by', '=', 'users.id')
         ->select(
            'tiendas_traslados.id as traslado_id',
            'productos.nombre',
            'productos.alias',
            'productos.codigo_barras as codigo',
            'tiendas.nombre as tienda_nombre',
            'tiendas.alias as tienda_alias',
            'tiendas_traslados.cantidad',
            'tiendas_traslados.tienda_stock_anterior',
            'tiendas_traslados.almacen_stock_anterior',
            'tiendas_traslados.tienda_stock_posterior',
            'tiendas_traslados.almacen_stock_posterior',
            'tiendas_traslados.created_at',
            'users.name as user_name'
         );

      if ($tiendaId) $query->where('tiendas_traslados.tienda_id', $tiendaId);
      
      if ($search) {
         $query->where(function ($q) use ($search) {
            $q->where('productos.nombre', 'like', "%{$search}%")
               ->orWhere('productos.alias', 'like', "%{$search}%")
               ->orWhere('productos.codigo_barras', 'like', "%{$search}%");
         });
      }
      
      if ($fecha) $query->where('tiendas_traslados.fecha', $fecha);

      $traslados = $query->orderByDesc('tiendas_traslados.id')->get()->map(function ($t) {
         $t->created_fmt = date('d/m/Y h:i A', strtotime($t->created_at));
         return $t;
      });

      return response()->json([
         'success' => true,
         'traslados' => $traslados
      ]);
   }
}
