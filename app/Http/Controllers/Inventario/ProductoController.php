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
       $productos = DB::table('productos')
                ->select('id', 'codigo_barras', 'nombre', 'costo_unitario', 'precio_unitario',
                'precio_minimo',
                'categoria_id', 
                'marca_id', 
                'estado')
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
        $producto->costo_unitario = $request->costo_unitario;
        $producto->precio_unitario = $request->precio_unitario;
        $producto->precio_minimo = $request->precio_minimo;
        $producto->marca_id = $request->marca_id;
        $producto->categoria_id = $request->categoria_id;
        $producto->save();

        $stocks = $request->input('stocks', []);

        foreach ($stocks as $tiendaId => $stock) {
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
        $producto->costo_unitario = $request->costo_unitario;   
        $producto->precio_unitario = $request->precio_unitario;
        $producto->precio_minimo = $request->precio_minimo;
        $producto->marca_id = $request->marca_id;
        $producto->categoria_id = $request->categoria_id;
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
}
