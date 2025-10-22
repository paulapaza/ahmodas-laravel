<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Models\Inventario\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function update(ProductoRequest  $request, $id)
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
    //buscarProducto
    public function buscarProducto(Request $request)
    {

        $stringBuscado = trim($request->stringSearch ?? '');

        if ($stringBuscado === '') {
            return response()->json(['error' => 'Búsqueda vacía'], 400);
        }

        //quitar espacio adelante y atraz
        $stringBuscado = trim($stringBuscado);
        $productoPorCodigo = Producto::where('codigo_barras', $stringBuscado)->first();
        return response()->json([$productoPorCodigo]);
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
}
