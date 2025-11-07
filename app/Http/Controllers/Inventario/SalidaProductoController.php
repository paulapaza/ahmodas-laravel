<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalidaProductos\BulkRequest;
use App\Services\SalidaProductoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalidaProductoController extends Controller
{
  // public function index()
  // {
  //   // 🔹 1️⃣ Obtener todas las tiendas (una sola vez)
  //   $tiendas = DB::table('tiendas')
  //     ->select('id', 'nombre')
  //     ->orderBy('id')
  //     ->get();

  //   // 🔹 2️⃣ Obtener productos con su stock por tienda
  //   $productos = DB::table('producto_tienda as pt')
  //     ->join('productos as p', 'pt.producto_id', '=', 'p.id')
  //     ->join('tiendas as t', 'pt.tienda_id', '=', 't.id')
  //     ->select(
  //       'p.id as producto_id',
  //       'p.codigo_barras',
  //       'p.nombre as producto_nombre',
  //       't.id as tienda_id',
  //       't.nombre as tienda_nombre',
  //       'pt.stock'
  //     )
  //     ->orderBy('p.id')
  //     ->orderBy('t.id')
  //     ->get();

  //   // 🔹 3️⃣ Agrupar productos y llenar tiendas faltantes con stock 0
  //   $result = [];
  //   foreach ($productos as $row) {
  //     $id = $row->producto_id;

  //     // Inicializa producto si no existe aún
  //     if (!isset($result[$id])) {
  //       // Copiamos todas las tiendas con stock 0 por defecto
  //       $result[$id] = [
  //         'id' => $id,
  //         'codigo_barras' => $row->codigo_barras,
  //         'nombre' => $row->producto_nombre,
  //         'tiendas' => $tiendas->map(fn($t) => [
  //           'id' => $t->id,
  //           'nombre' => $t->nombre,
  //           'stock' => 0
  //         ])->toArray()
  //       ];
  //     }

  //     // Actualizamos el stock de la tienda correspondiente
  //     foreach ($result[$id]['tiendas'] as &$tienda) {
  //       if ($tienda['id'] === $row->tienda_id) {
  //         $tienda['stock'] = $row->stock;
  //         break;
  //       }
  //     }
  //   }
  //   unset($tienda); // 🔸 para evitar referencia residual

  //   $result = array_values($result); // resetear índices

  //   return response()->json($result, 200);
  // }

  protected $salidaProductoService;

  public function __construct(SalidaProductoService $salidaProductoService)
  {
    $this->salidaProductoService = $salidaProductoService;
  }

  public function index()
  {
    // Obtener todas las tiendas (una sola vez)
    $tiendas = DB::table('tiendas')
      ->select('id', 'nombre')
      ->orderBy('id')
      ->get();

    // Obtener productos con su stock por tienda
    $productos = DB::table('productos as p')
      ->leftJoin('producto_tienda as pt', 'p.id', '=', 'pt.producto_id')
      ->leftJoin('tiendas as t', 'pt.tienda_id', '=', 't.id')
      ->select(
        'p.id as producto_id',
        'p.codigo_barras',
        'p.alias as producto_alias',
        'p.nombre as producto_nombre',
        't.id as tienda_id',
        't.nombre as tienda_nombre',
        DB::raw('COALESCE(pt.stock, 0) as stock') // si no hay stock, devuelve 0
      )
      ->orderBy('p.id')
      ->orderBy('t.id')
      ->get();

    // Agrupar productos y rellenar stock faltante
    $result = [];
    foreach ($productos as $row) {
      $id = $row->producto_id;

      if (!isset($result[$id])) {
        $result[$id] = [
          'id' => $id,
          'codigo_barras' => $row->codigo_barras,
          'nombre' => $row->producto_nombre,
          'alias' => $row->producto_alias,
          'tiendas' => $tiendas->map(fn($t) => [
            'id' => $t->id,
            'nombre' => $t->nombre,
            'stock' => 0
          ])->toArray()
        ];
      }

      foreach ($result[$id]['tiendas'] as &$tienda) {
        if ($tienda['id'] === $row->tienda_id) {
          $tienda['stock'] = $row->stock;
          break;
        }
      }
    }
    unset($tienda);
    $result = array_values($result);

    // Convertir a DataTable
    return DataTables::of(collect($result))
      ->make(true);
  }

  public function store(BulkRequest $request)
  {
    $data = $request->validated();

    try {
      DB::beginTransaction();

      // Insertar todas las salidas
      $this->salidaProductoService->bulk($data);

      // Actualizar stock en producto_tienda
      $now = now();
      foreach ($data as $item) {
        DB::table('producto_tienda')->upsert(
          [
            [
              'producto_id' => $item['producto_id'],
              'tienda_id' => $item['tienda_id'],
              'stock' => $item['stock_despues'],
              'created_at' => $now,
              'updated_at' => $now,
            ],
          ],
          ['producto_id', 'tienda_id'],
          ['stock', 'updated_at']
        );
      }

      DB::commit();

      return response()->json([
        'message' => 'Salidas registradas y stock actualizado correctamente',
      ], 201);
    } catch (\Throwable $e) {
      DB::rollBack();
      return response()->json([
        'error'  => 'Error al registrar las salidas',
        'detail' => $e->getMessage(),
      ], 500);
    }
  }

  public function history($producto_id)
  {
    $salidas = DB::table('salida_productos as sp')
      ->join('productos as p', 'sp.producto_id', '=', 'p.id')
      ->join('tiendas as t', 'sp.tienda_id', '=', 't.id')
      ->select(
        'sp.id',
        'p.codigo_barras',
        'p.nombre as producto_nombre',
        't.id as tienda_id',
        't.nombre as tienda_nombre',
        'sp.cantidad_reducida',
        'sp.stock_antes',
        'sp.stock_despues',
        'sp.tipo',
        'sp.pos_order_id as venta_id',
        'sp.comentario',
        'sp.created_at'
      )
      ->where('sp.producto_id', $producto_id) // 👈 filtro por producto
      ->orderBy('sp.id', 'desc')
      ->get();

    return response()->json(['data' => $salidas], 201);
  }

  public function getListadoTiendas() {
    $tiendas = DB::table('tiendas')
      ->select('id', 'nombre')
      ->orderBy('nombre')
      ->get();

    return response()->json(['data' => $tiendas], 200);
  }
}
