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
            ->select(
                'id',
                'codigo_barras',
                'nombre',
                'costo_unitario',
                'precio_unitario',
                'precio_minimo',
                'categoria_id',
                'marca_id',
                'estado'
            )
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
    //buscarProducto
    public function buscarProducto(Request $request)
    {

        $stringBuscado = trim($request->stringSearch ?? '');

        if ($stringBuscado === '') {
            return response()->json(['error' => 'Búsqueda vacía'], 400);
        }

        /* ─────────────────────────────────────────────
     | 1. Intento exacto por código de barras
     ───────────────────────────────────────────── */
        // Si la cadena son solo dígitos asumimos código de barras y no tiene espacios en blanco
        if (preg_match('/^\d+$/', $stringBuscado)) {
            $productoPorCodigo = Producto::where('codigo_barras', $stringBuscado)->first();

            if ($productoPorCodigo) {
                return response()->json([$productoPorCodigo]);
            }
            // Si no se encontró por código seguimos con búsqueda por nombre
        }

        /* ─────────────────────────────────────────────
     | 2. Búsqueda flexible por nombre
     ───────────────────────────────────────────── */
        // Separamos por espacios, quitamos duplicados y tokens muy cortos
        $tokens = collect(preg_split('/\s+/', $stringBuscado))
            ->filter(fn($t) => strlen($t) > 1)   // ignora tokens de 1 letra
            ->unique()
            ->values();

        // Construimos la consulta: todas las palabras deben estar presentes (AND)
        $productos = Producto::where(function ($q) use ($tokens) {
            foreach ($tokens as $token) {
                $q->where('nombre', 'LIKE', '%' . $token . '%');
            }
        })
            ->limit(20) // aumenta o pagina si lo necesitas
            ->get();

        if ($productos->isEmpty()) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }

        return response()->json($productos);
    }
}
