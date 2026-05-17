<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Models\Inventario\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductoController extends Controller
{
  public function index()
  {
    $productos = DB::table('productos as p')
      ->join('categorias as c', 'p.categoria_id', '=', 'c.id')
      ->join('marcas as m', 'p.marca_id', '=', 'm.id')
      ->leftJoin('producto_tienda as pt', 'p.id', '=', 'pt.producto_id')
      ->select(
        'p.id',
        'p.codigo_barras',
        'p.nombre',
        'p.alias',
        'p.costo_unitario',
        'p.precio_unitario',
        'p.precio_minimo',
        'p.categoria_id',
        'p.marca_id',
        'p.precio_x_mayor',
        'p.tipo_de_igv',
        'p.moneda',
        'p.estado',
        'p.created_at',
        'c.nombre as categoria_nombre',
        'm.nombre as marca_nombre',
        DB::raw('COALESCE(SUM(pt.stock), 0) as total_stock'),
        DB::raw("EXISTS (SELECT 1 FROM salida_productos sp WHERE sp.producto_id = p.id) as tiene_salida")
      )
      ->groupBy(
        'p.id',
        'p.codigo_barras',
        'p.nombre',
        'p.alias',
        'p.costo_unitario',
        'p.precio_unitario',
        'p.precio_minimo',
        'p.categoria_id',
        'p.marca_id',
        'p.precio_x_mayor',
        'p.tipo_de_igv',
        'p.moneda',
        'p.estado',
        'p.created_at',
        'c.nombre',
        'm.nombre'
      )
      ->orderBy('p.nombre')
      ->get();

    return response()->json($productos, 200);
  }
  //show
  public function show($id)
  {
    $producto = Producto::find($id);

    return response()->json($producto, 200);
  }
  public function store(ProductoRequest $request)
  {

    $producto = new Producto();
    $producto->uid = $request->uid;
    $producto->codigo_barras = $request->codigo_barras;
    $producto->nombre = $request->nombre;
    $producto->alias = $request->alias;
    $producto->costo_unitario = $request->costo_unitario;
    $producto->precio_unitario = $request->precio_unitario;
    $producto->precio_minimo = $request->precio_minimo;
    $producto->precio_x_mayor = $request->precio_x_mayor;
    $producto->marca_id = $request->marca_id;
    $producto->categoria_id = $request->categoria_id;
    $producto->tipo_de_igv = $request->tipo_de_igv;
    $producto->save();

    $stocks = $request->input('stocks', []);

    foreach ($stocks as $tiendaId => $stock) {
      //SI EL STOK ES NULL O VACIO, SE ASIGNA 0
      if (is_null($stock) || $stock === '') {
        $stock = 0;
      }
      $producto->tiendas()->attach($tiendaId, ['stock' => $stock]);
    }

    return response()->json([
      "success" => true,
      "message" => "Producto creado correctamente",

    ], 201);
  }
  //update
  public function update(ProductoRequest $request, $id)
  {
    $producto = Producto::find($id);
    if (!$producto) {
      return response()->json([
        "success" => false,
        "message" => "Producto no encontrado",
      ], 404);
    }
    $producto->codigo_barras = $request->codigo_barras;
    $producto->nombre = $request->nombre;
    $producto->alias = $request->alias;
    $producto->costo_unitario = $request->costo_unitario;
    $producto->precio_unitario = $request->precio_unitario;
    $producto->precio_minimo = $request->precio_minimo;
    $producto->precio_x_mayor = $request->precio_x_mayor;
    $producto->marca_id = $request->marca_id;
    $producto->categoria_id = $request->categoria_id;
    $producto->tipo_de_igv = $request->tipo_de_igv;
    $producto->save();
    // Actualizar stocks
    $stocks = $request->input('stocks', []);
    foreach ($stocks as $tiendaId => $stock) {
      // Verificar si la tienda ya está asociada al producto
      if ($producto->tiendas()->where('tienda_id', $tiendaId)->exists()) {
        // Actualizar stock existente
        $producto->tiendas()->updateExistingPivot($tiendaId, ['stock' => $stock]);
      } else {
        // Asociar nueva tienda con stock
        $producto->tiendas()->attach($tiendaId, ['stock' => $stock]);
      }
    }
    // Eliminar tiendas que no están en el request
    $tiendasExistentes = $producto->tiendas->pluck('id')->toArray();
    $tiendasRequest = array_keys($stocks);
    $tiendasAEliminar = array_diff($tiendasExistentes, $tiendasRequest);
    foreach ($tiendasAEliminar as $tiendaId) {
      $producto->tiendas()->detach($tiendaId);
    }


    return response()->json([
      "success" => true,
      "message" => "Producto actualizado correctamente",

    ], 201);
  }
  public function buscarProducto(Request $request)
  {
    $search = $request->input('query') ?? $request->input('stringSearch') ?? $request->input('q') ?? '';
    $search = trim($search);

    if ($search === '') {
      return response()->json([]);
    }

    // Obtener la tienda actual, pero solo usarla como filtro estricto si se solicita (ej. POS/Devoluciones)
    // Para Kardex o Transacciones, permitimos busqueda global si no se pasa tienda_id explicitamente.
    $tiendaId = $request->input('tienda_id');
    
    // Si viene del POS (product-search.js usa stringSearch) y no envia tienda_id, forzamos la tienda del usuario
    if (!$tiendaId && $request->has('stringSearch')) {
        $tiendaId = Auth::check() ? Auth::user()->tienda_id : null;
    }

    $query = Producto::with('tiendas')
      ->where(function($q) use ($search) {
          $q->where('nombre', 'LIKE', "%{$search}%")
            ->orWhere('alias', 'LIKE', "%{$search}%")
            ->orWhere('codigo_barras', 'LIKE', "%{$search}%");
      });

    // Filtrar estrictamente a los productos que pertenecen a la tienda actual
    // EXCEPTO si estamos buscando un producto para DEVOLVER (tipo_busqueda = 'devuelto')
    $tipoBusqueda = $request->input('tipo_busqueda');
    if ($tiendaId && $tipoBusqueda !== 'devuelto') {
        $query->whereHas('tiendas', function($q) use ($tiendaId) {
            $q->where('tienda_id', $tiendaId);
        });
    }

    $productos = $query->limit(20)->get();

    // Calcular el stock para la tienda actual (o 0 si no hay tienda contexto)
    $contextTiendaId = $tiendaId ?? (Auth::check() ? Auth::user()->tienda_id : null);
    $productos->each(function ($producto) use ($contextTiendaId) {
        $producto->stock_actual = $contextTiendaId ? $producto->stockEnTienda($contextTiendaId) : 0;
    });

    return response()->json($productos);
  }
  // eliminar
  public function destroy($id)
  {
    $producto = Producto::find($id);
    if (!$producto) {
      return response()->json([
        "success" => false,
        "message" => "Producto no encontrado",
      ], 404);
    }
    // Eliminar las relaciones con tiendas
    $producto->tiendas()->detach();
    // Eliminar el producto
    $producto->delete();

    return response()->json([
      "success" => true,
      "message" => "Producto eliminado correctamente",
    ]);
  }

  public function productosTienda()
  {
    $tiendaId = Auth::user()->tienda_id;

    $productos = DB::table('productos as p')
      ->join('producto_tienda as pt', 'p.id', '=', 'pt.producto_id')
      ->where('pt.tienda_id', $tiendaId)
      ->select(
        'p.id',
        'p.nombre',
        'p.alias',
        'p.precio_unitario',
        'p.precio_minimo',
        'p.precio_x_mayor',
        DB::raw('COALESCE(SUM(pt.stock), 0) as total_stock')
      )
      ->groupBy('p.id', 'p.nombre', 'p.alias', 'p.precio_unitario', 'p.precio_minimo', 'p.precio_x_mayor')
      ->get();

    return response()->json($productos, 200);
  }

  public function _importExcel(Request $request)
  {
    $request->validate([
      'excel' => 'required|file|mimes:xlsx,xls'
    ]);

    $path = $request->file('excel')->getRealPath();

    // Lector optimizado (solo datos)
    $reader = IOFactory::createReaderForFile($path);
    $reader->setReadDataOnly(true);

    $spreadsheet = $reader->load($path);
    $sheet = $spreadsheet->getActiveSheet();

    $startRow = 3;
    $totalColumns = 8;

    $data = [];

    $highestRow = $sheet->getHighestRow();

    for ($row = $startRow; $row <= $highestRow; $row++) {

      $rowData = [];

      for ($col = 1; $col <= $totalColumns; $col++) {
        $columnLetter = Coordinate::stringFromColumnIndex($col);
        // $rowData[] = $sheet->getCell($columnLetter . $row)->getValue();
        $cell = $sheet->getCell($columnLetter . $row);

        $value = $cell->isFormula()
          ? $cell->getCalculatedValue()
          : $cell->getValue();

        $rowData[] = $value;
      }

      // Evitar filas totalmente vacías
      if (array_filter($rowData)) {
        $data[] = $rowData;
      }
    }

    return $data;
  }

  private function _resolveStockPorTienda(array $row, int $productoId): array
  {
    $map = [
      5 => 4,
      6 => 1,
      7 => 2,
      8 => 3,
    ];

    $now = now();
    $result = [];

    foreach ($map as $index => $tiendaId) {
      if (\array_key_exists($index, $row) && $row[$index] !== null) {
        $result[] = [
          'producto_id' => $productoId,
          'tienda_id' => $tiendaId,
          'stock' => (int) $row[$index],
          'created_at' => $now,
          'updated_at' => $now,
        ];
      }
    }

    return $result;
  }

  public function guardarProductosDesdeExcel(Request $request)
  {
    $datosExcel = $this->_importExcel($request);

    DB::beginTransaction();

    try {
      foreach ($datosExcel as $row) {
        $insertedId = DB::table('productos')->insertGetId([
          'uid' => Str::ulid()->toString(),
          'codigo_barras' => trim($row[0]),
          'nombre' => trim($row[4]),
          'alias' => trim($row[4]),
          'costo_unitario' => $row[1],
          'precio_unitario' => $row[2],
          'precio_minimo' => $row[3],
          'marca_id' => 1,
          'categoria_id' => 1,
          'created_at' => now(),
          'updated_at' => now(),
        ]);

        $stockPorTienda = $this->_resolveStockPorTienda($row, $insertedId);

        DB::table('producto_tienda')->insert($stockPorTienda);
      }

      DB::commit();

      return response()->json([
        'message' => 'Productos importados correctamente',
        'total' => \count($datosExcel),
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'message' => 'Error al importar productos: ' . $e->getMessage(),
      ], 500);
    }
  }
}
