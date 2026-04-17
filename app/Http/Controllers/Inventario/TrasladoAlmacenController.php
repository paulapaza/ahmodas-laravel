<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Inventario\TrasladoAlmacenStoreRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TrasladoAlmacenController extends Controller
{
   public function index()
   {
      return view('modules.inventario.traslados-almacen.gestionar');
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
      $fechaFiltro = $request->input('fecha') ?: now()->toDateString();
      $almacenId = $this->getAlmacenId();

      // Tiendas destino (excluyendo la principal)
      $tiendas = DB::table('tiendas')
         ->where('id', '!=', $almacenId)
         ->where('estado', 1)
         ->select('id', 'nombre')
         ->orderBy('nombre')
         ->get();
      // Productos con su stock en el Almacén Principal
      $productos = DB::table('productos')
         ->leftJoin('producto_tienda', function ($join) use ($almacenId) {
            $join->on('productos.id', '=', 'producto_tienda.producto_id')
               ->where('producto_tienda.tienda_id', '=', $almacenId);
         })
         ->where('productos.estado', 1)
         ->select('productos.id', 'productos.nombre', 'productos.alias', 'productos.codigo_barras as codigo', DB::raw('COALESCE(producto_tienda.stock, 0) as stock'))
         ->get();

      // Mapa de stock de todos los productos en todas las tiendas (solo columnas necesarias)
      $stockMap = DB::table('producto_tienda')
         ->select('producto_id', 'tienda_id', 'stock')
         ->get()
         ->groupBy('producto_id')
         ->map(function ($items) {
            return $items->keyBy('tienda_id')->map->stock;
         });

      // Traslados ya confirmados según la fecha (desde BD usando columna fecha)
      $confirmadosHoy = DB::table('almacen_traslados')
         ->join('productos', 'almacen_traslados.producto_id', '=', 'productos.id')
         ->join('tiendas', 'almacen_traslados.tienda_id', '=', 'tiendas.id')
         ->where('almacen_traslados.fecha', $fechaFiltro)
         ->select(
            'almacen_traslados.id as traslado_id',
            'almacen_traslados.producto_id as id',
            'productos.nombre',
            'productos.alias',
            'productos.codigo_barras as codigo',
            'almacen_traslados.tienda_id',
            'tiendas.nombre as tienda_nombre',
            'tiendas.alias as tienda_alias',
            'almacen_traslados.stock_vendido as vendido',
            'almacen_traslados.stock_disponible as disponible',
            'almacen_traslados.created_at as fecha',
            'almacen_traslados.updated_at'
         )
         ->orderByDesc('almacen_traslados.id')
         ->get()
         ->map(function ($t) {
            $t->confirmado = true;
            return $t;
         });

      return response()->json([
         'productos' => $productos,
         'stockMap' => $stockMap,
         'tiendas' => $tiendas,
         'confirmados' => $confirmadosHoy
      ]);
   }

   public function store(TrasladoAlmacenStoreRequest $request)
   {
      try {
         $traslados = $request->input('traslados');
         $hoy = now();
         $almacenId = $this->getAlmacenId();
         $userId = $request->input('user_id') ?? auth()->id();

         // 1. PRE-VALIDACIÓN DE STOCK TOTAL POR PRODUCTO
         // Agrupamos por producto para saber cuánto stock total necesitamos de cada uno en este lote
         $totalesPorProducto = [];
         foreach ($traslados as $t) {
            $pid = $t['producto_id'];
            $totalesPorProducto[$pid] = ($totalesPorProducto[$pid] ?? 0) + $t['stock_disponible'];
         }

         // Consultamos stock actual de todos los productos involucrados en el Almacén Principal
         $stocksAlmacen = DB::table('producto_tienda')
            ->where('tienda_id', $almacenId)
            ->whereIn('producto_id', array_keys($totalesPorProducto))
            ->get()
            ->keyBy('producto_id');

         foreach ($totalesPorProducto as $productoId => $cantidadTotalRequerida) {
            $stockActual = $stocksAlmacen->get($productoId)->stock ?? 0;

            if ($stockActual < $cantidadTotalRequerida) {
               $productoNombre = DB::table('productos')->where('id', $productoId)->value('nombre');
               throw new \Exception("Stock insuficiente para '{$productoNombre}'. Requerido en total: {$cantidadTotalRequerida}, Disponible en Almacén: {$stockActual}.");
            }
         }

         // 2. PROCESAMIENTO ATÓMICO
         DB::beginTransaction();

         foreach ($traslados as $t) {
            // Bloqueo y obtención de stock actualizado (dentro de transacción)
            $almacenStock = DB::table('producto_tienda')
               ->where('producto_id', $t['producto_id'])
               ->where('tienda_id', $almacenId)
               ->lockForUpdate()
               ->first();

            // (Segunda capa de seguridad ante condiciones de carrera)
            if (!$almacenStock || $almacenStock->stock < $t['stock_disponible']) {
               throw new \Exception("Stock insuficiente en almacén durante el procesamiento para el producto ID: " . $t['producto_id']);
            }

            // Descontar del Almacén Principal
            DB::table('producto_tienda')
               ->where('id', $almacenStock->id)
               ->decrement('stock', $t['stock_disponible']);

            // Aumentar en la Tienda Destino
            $tiendaStock = DB::table('producto_tienda')
               ->where('producto_id', $t['producto_id'])
               ->where('tienda_id', $t['tienda_id'])
               ->lockForUpdate()
               ->first();

            if ($tiendaStock) {
               $stockTiendaAnterior = $tiendaStock->stock;
               DB::table('producto_tienda')
                  ->where('id', $tiendaStock->id)
                  ->increment('stock', $t['stock_disponible']);
            } else {
               $stockTiendaAnterior = 0;
               DB::table('producto_tienda')->insert([
                  'producto_id' => $t['producto_id'],
                  'tienda_id' => $t['tienda_id'],
                  'stock' => $t['stock_disponible'],
                  'created_at' => $hoy,
                  'updated_at' => $hoy,
               ]);
            }

            // 3. LÓGICA DE PERSISTENCIA (SIEMPRE INSERTAR)
            $trasladoId = DB::table('almacen_traslados')->insertGetId([
               'created_by' => $userId,
               'updated_by' => $userId,
               'almacen_id' => $almacenId,
               'tienda_id' => $t['tienda_id'],
               'producto_id' => (int)$t['producto_id'],
               'almacen_stock_anterior' => $almacenStock->stock,
               'tienda_stock_anterior' => $stockTiendaAnterior,
               'almacen_stock_posterior' => $almacenStock->stock - $t['stock_disponible'],
               'tienda_stock_posterior' => $stockTiendaAnterior + $t['stock_disponible'],
               'stock_disponible' => $t['stock_disponible'],
               'fecha' => $hoy->toDateString(),
               'created_at' => $hoy,
               'updated_at' => $hoy,
            ]);

            // Registrar Historial (Fotografía del stock en este momento)
            // Nota: Aquí sumamos lo que hay en el registro actual a lo que ya existe en la tienda hoy
            $stockYaConfirmado = DB::table('almacen_traslados')
                ->where('producto_id', (int)$t['producto_id'])
                ->where('tienda_id', $t['tienda_id'])
                ->where('fecha', $hoy->toDateString())
                ->sum('stock_disponible');

            DB::table('almacen_traslados_historial')->insert([
               'almacen_traslado_id' => $trasladoId,
               'created_by' => $userId,
               'stock_disponible' => $stockYaConfirmado,
               'stock_vendido' => 0,
               'stock_almacen' => $almacenStock->stock - $t['stock_disponible'],
               'created_at' => $hoy,
            ]);
         }

         DB::commit();

         return response()->json([
            'success' => true,
            'user_id' => $userId,
            'message' => 'Traslados procesados correctamente e inventario actualizado.'
         ]);

      } catch (\Exception $e) {
         if (DB::transactionLevel() > 0)
            DB::rollBack();
         return response()->json([
            'success' => false,
            'message' => 'Error al procesar traslados: ' . $e->getMessage()
         ], 500);
      }
   }

   public function actualizarStock(Request $request)
   {
      try {
         $trasladoId = $request->input('traslado_id');
         $cantidadAgregar = (int) $request->input('cantidad_agregada');
         $hoy = now();
         $almacenId = $this->getAlmacenId();
         $userId = $request->input('user_id') ?? auth()->id();

         if ($cantidadAgregar <= 0)
            throw new \Exception("La cantidad a agregar debe ser mayor a 0.");

         DB::beginTransaction();

         $traslado = DB::table('almacen_traslados')->where('id', $trasladoId)->lockForUpdate()->first();
         if (!$traslado)
            throw new \Exception("Traslado no encontrado.");

         // 1. Validar Stock en Almacén Principal
         $almacenPT = DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $almacenId)
            ->lockForUpdate()
            ->first();

         if (!$almacenPT || $almacenPT->stock < $cantidadAgregar) {
            throw new \Exception("Stock insuficiente en almacén para enviar más unidades.");
         }

         // 2. Ejecutar Movimiento
         DB::table('producto_tienda')->where('id', $almacenPT->id)->decrement('stock', $cantidadAgregar);

         $tiendaPT = DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $traslado->tienda_id)
            ->lockForUpdate()
            ->first();

         if ($tiendaPT) {
            DB::table('producto_tienda')->where('id', $tiendaPT->id)->increment('stock', $cantidadAgregar);
         } else {
            DB::table('producto_tienda')->insert([
               'producto_id' => $traslado->producto_id,
               'tienda_id' => $traslado->tienda_id,
               'stock' => $cantidadAgregar,
               'created_at' => $hoy,
               'updated_at' => $hoy
            ]);
         }

         // 3. Actualizar Maestro
         $nuevoDisponible = $traslado->stock_disponible + $cantidadAgregar;
         DB::table('almacen_traslados')
            ->where('id', $trasladoId)
            ->update([
               'updated_by' => $userId,
               'stock_disponible' => $nuevoDisponible,
               'updated_at' => $hoy
            ]);

         // 4. Historial
         DB::table('almacen_traslados_historial')->insert([
            'almacen_traslado_id' => $trasladoId,
            'created_by' => $userId,
            'stock_disponible' => $nuevoDisponible,
            'stock_vendido' => $traslado->stock_vendido,
            'stock_almacen' => $almacenPT->stock - $cantidadAgregar,
            'created_at' => $hoy,
         ]);

         DB::commit();
         return response()->json(['success' => true, 'message' => "Se agregaron {$cantidadAgregar} unidades correctamente."]);

      } catch (\Exception $e) {
         if (DB::transactionLevel() > 0)
            DB::rollBack();
         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
   }

   public function eliminarTraslado(Request $request)
   {
      try {
         $trasladoId = $request->input('traslado_id');
         $almacenId = $this->getAlmacenId();
         $userId = $request->input('user_id') ?? auth()->id();

         DB::beginTransaction();

         $traslado = DB::table('almacen_traslados')->where('id', $trasladoId)->lockForUpdate()->first();
         if (!$traslado)
            throw new \Exception("Traslado no encontrado.");

         $disponible = $traslado->stock_disponible;

         // 1. Devolver disponible al Almacén Principal
         DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $almacenId)
            ->increment('stock', $disponible);

         // 2. Restar disponible de la Tienda
         DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $traslado->tienda_id)
            ->decrement('stock', $disponible);

         // 3. Eliminar Definitivamente (Hard Delete)
         // Esto activará el onDelete('cascade') en la base de datos y borrará el historial automáticamente.
         DB::table('almacen_traslados')->where('id', $trasladoId)->delete();

         DB::commit();
         return response()->json(['success' => true, 'message' => 'Registro eliminado y stock devuelto al almacén.']);

      } catch (\Exception $e) {
         if (DB::transactionLevel() > 0)
            DB::rollBack();
         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
   }

   public function actualizarVenta(Request $request)
   {
      try {
         $trasladoId = $request->input('traslado_id');
         $nuevaVenta = (int) $request->input('nueva_venta');
         $hoy = now();
         $almacenId = $this->getAlmacenId();
         $userId = $request->input('user_id') ?? auth()->id();

         if ($nuevaVenta < 0)
            throw new \Exception("La cantidad no puede ser negativa.");

         DB::beginTransaction();

         $traslado = DB::table('almacen_traslados')->where('id', $trasladoId)->lockForUpdate()->first();
         if (!$traslado)
            throw new \Exception("Traslado no encontrado.");

         $delta = $nuevaVenta - $traslado->stock_vendido;

         // Validar que el delta no supere lo disponible
         if ($traslado->stock_disponible < $delta) {
            throw new \Exception("No hay suficiente stock disponible en el traslado para marcar esta venta.");
         }

         // 1. Ajustar Inventario Físico de la Tienda
         DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $traslado->tienda_id)
            ->decrement('stock', $delta);

         // 2. Actualizar Registro de Traslado
         $nuevoDisponible = $traslado->stock_disponible - $delta;
         DB::table('almacen_traslados')
            ->where('id', $trasladoId)
            ->update([
               'stock_vendido' => $nuevaVenta,
               'stock_disponible' => $nuevoDisponible,
               'updated_by' => $userId,
               'updated_at' => $hoy
            ]);

         // 3. Historial (Obtenemos el stock de almacén actual tras la venta)
         $stockAlmacen = DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $almacenId)
            ->value('stock');

         DB::table('almacen_traslados_historial')->insert([
            'almacen_traslado_id' => $trasladoId,
            'created_by' => $userId,
            'stock_disponible' => $nuevoDisponible,
            'stock_vendido' => $nuevaVenta,
            'stock_almacen' => $stockAlmacen,
            'created_at' => $hoy,
         ]);

         DB::commit();
         return response()->json(['success' => true, 'message' => 'Venta actualizada correctamente.']);

      } catch (\Exception $e) {
         if (DB::transactionLevel() > 0)
            DB::rollBack();
         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
   }

   public function actualizarDevolucion(Request $request)
   {
      try {
         $trasladoId = $request->input('traslado_id');
         $cantidadRegresar = (int) $request->input('cantidad_a_regresar');
         $hoy = now();
         $almacenId = $this->getAlmacenId();
         $userId = $request->input('user_id') ?? auth()->id();

         if ($cantidadRegresar <= 0)
            throw new \Exception("La cantidad a regresar debe ser mayor a 0.");

         DB::beginTransaction();

         $traslado = DB::table('almacen_traslados')->where('id', $trasladoId)->lockForUpdate()->first();
         if (!$traslado)
            throw new \Exception("Traslado no encontrado.");

         // Validar que no se regrese más de lo disponible
         if ($traslado->stock_disponible < $cantidadRegresar) {
            throw new \Exception("No puedes regresar más unidades de las que hay disponibles en tienda ({$traslado->stock_disponible}).");
         }

         // 1. Devolver al Almacén Principal
         DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $almacenId)
            ->increment('stock', $cantidadRegresar);

         // 2. Restar de la Tienda
         DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $traslado->tienda_id)
            ->decrement('stock', $cantidadRegresar);

         // 3. Actualizar Registro de Traslado
         $nuevoDisponible = $traslado->stock_disponible - $cantidadRegresar;
         DB::table('almacen_traslados')
            ->where('id', $trasladoId)
            ->update([
               'updated_by' => $userId,
               'stock_disponible' => $nuevoDisponible,
               'updated_at' => $hoy
            ]);

         // 4. Historial
         $stockAlmacen = DB::table('producto_tienda')
            ->where('producto_id', $traslado->producto_id)
            ->where('tienda_id', $almacenId)
            ->value('stock');

         DB::table('almacen_traslados_historial')->insert([
            'almacen_traslado_id' => $trasladoId,
            'created_by' => $userId,
            'stock_disponible' => $nuevoDisponible,
            'stock_vendido' => $traslado->stock_vendido,
            'stock_almacen' => $stockAlmacen,
            'created_at' => $hoy,
         ]);

         DB::commit();
         return response()->json(['success' => true, 'message' => "Se regresaron {$cantidadRegresar} unidades al almacén."]);

      } catch (\Exception $e) {
         if (DB::transactionLevel() > 0)
            DB::rollBack();
         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
   }

   public function getHistorial(Request $request)
   {
      $trasladoId = $request->get('traslado_id');

      $historial = DB::table('almacen_traslados_historial')
         ->where('almacen_traslado_id', $trasladoId)
         ->select(
            'stock_vendido as vendido',
            'stock_disponible as disponible',
            'stock_almacen as almacen',
            'created_at as fecha'
         )
         ->orderByDesc('created_at')
         ->get()
         ->map(function ($h) {
            $h->fecha_formateada = date('d/m/Y H:i', strtotime($h->fecha));
            return $h;
         });

      return response()->json([
         'success' => true,
         'historial' => $historial
      ]);
   }

   public function getHistorialGlobal(Request $request)
   {
      $almacenId = $this->getAlmacenId();
      $tiendaId = $request->get('tienda_id');
      $fecha = $request->get('fecha');
      $search = $request->get('search');

      $query = DB::table('almacen_traslados')
         ->join('productos', 'almacen_traslados.producto_id', '=', 'productos.id')
         ->join('tiendas', 'almacen_traslados.tienda_id', '=', 'tiendas.id')
         ->leftJoin('users', 'almacen_traslados.created_by', '=', 'users.id')
         ->leftJoin('producto_tienda as pt_almacen', function ($join) use ($almacenId) {
            $join->on('almacen_traslados.producto_id', '=', 'pt_almacen.producto_id')
               ->where('pt_almacen.tienda_id', '=', $almacenId);
         })
         ->select(
            'almacen_traslados.id as traslado_id',
            'productos.nombre',
            'productos.alias',
            'productos.codigo_barras as codigo',
            'tiendas.nombre as tienda_nombre',
            'tiendas.alias as tienda_alias',
            'almacen_traslados.tienda_id',
            'almacen_traslados.stock_vendido as vendido',
            'almacen_traslados.stock_disponible as disponible',
            'almacen_traslados.created_at',
            'almacen_traslados.updated_at',
            DB::raw('COALESCE(pt_almacen.stock, 0) as stock_almacen'),
            'users.name as user_name',
            'users.email as user_email'
         );

      if ($tiendaId)
         $query->where('almacen_traslados.tienda_id', $tiendaId);
      if ($search) {
         $query->where(function ($q) use ($search) {
            $q->where('productos.nombre', 'like', "%{$search}%")
               ->orWhere('productos.alias', 'like', "%{$search}%")
               ->orWhere('productos.codigo_barras', 'like', "%{$search}%");
         });
      }
      if ($fecha)
         $query->where('almacen_traslados.fecha', $fecha);

      $traslados = $query->orderByDesc('almacen_traslados.id')->get()->map(function ($t) {
         $t->created_fmt = date('d/m/Y H:i', strtotime($t->created_at));
         $t->updated_fmt = date('d/m/Y H:i', strtotime($t->updated_at));
         return $t;
      });

      // También necesitamos las tiendas para el filtro
      $tiendas = DB::table('tiendas')
         ->where('id', '!=', $almacenId)
         ->where('estado', 1)
         ->select('id', 'nombre')
         ->orderBy('nombre')
         ->get();

      return response()->json([
         'success' => true,
         'traslados' => $traslados,
         'tiendas' => $tiendas
      ]);
   }

   public function historial()
   {
      return view('modules.inventario.traslados-almacen.historial');
   }

   public function importarStockExcel(Request $request)
   {
      $request->validate([
         'archivo' => 'required|max:10240', // Validamos tamaño, el formato lo manejará el lector
      ]);

      try {
         $archivo = $request->file('archivo');
         $spreadsheet = IOFactory::load($archivo->getRealPath());
         $worksheet = $spreadsheet->getActiveSheet();
         $rows = $worksheet->toArray();

         if (count($rows) < 2) {
            throw new \Exception("El archivo está vacío o no tiene el formato correcto.");
         }

         $header = array_map('trim', array_shift($rows));
         $colProductId = array_search('productid', $header);
         $colCount = array_search('count', $header);

         if ($colProductId === false || $colCount === false) {
            throw new \Exception("No se encontraron las columnas obligatorias 'productid' (Código de Barras) o 'count' (Cantidad).");
         }

         $warehouseId = DB::table('tiendas')->where('es_almacen', 1)->value('id');
         if (!$warehouseId) {
            throw new \Exception("No se ha configurado ninguna tienda como Almacén Principal.");
         }

         $successCount = 0;
         $errors = [];
         $codigosFaltantes = [];
         $hoy = now();

         DB::beginTransaction();
         foreach ($rows as $index => $row) {
            $barcode = isset($row[$colProductId]) ? trim($row[$colProductId]) : null;
            $count = isset($row[$colCount]) ? (int)$row[$colCount] : 0;

            if (empty($barcode)) continue;
            if ($count <= 0) continue;

            $producto = DB::table('productos')->where('codigo_barras', $barcode)->first();

            if (!$producto) {
               $errors[] = "Línea " . ($index + 2) . ": Código '$barcode' no existe.";
               if (!in_array($barcode, $codigosFaltantes)) {
                  $codigosFaltantes[] = $barcode;
               }
               continue;
            }

            // Upsert stock en el almacén
            $pt = DB::table('producto_tienda')
               ->where('producto_id', $producto->id)
               ->where('tienda_id', $warehouseId)
               ->first();

            if ($pt) {
               DB::table('producto_tienda')
                  ->where('id', $pt->id)
                  ->increment('stock', $count, ['updated_at' => $hoy]);
            } else {
               DB::table('producto_tienda')->insert([
                  'producto_id' => $producto->id,
                  'tienda_id' => $warehouseId,
                  'stock' => $count,
                  'created_at' => $hoy,
                  'updated_at' => $hoy
               ]);
            }

            $successCount++;
         }
         DB::commit();

         return response()->json([
            'success' => true,
            'message' => "Se importaron $successCount productos con éxito.",
            'errors' => $errors,
            'codigos_faltantes' => $codigosFaltantes
         ]);

      } catch (\Exception $e) {
         if (DB::transactionLevel() > 0) DB::rollBack();
         return response()->json([
            'success' => false,
            'message' => "Error al procesar el Excel: " . $e->getMessage()
         ], 422);
      }
   }
}
