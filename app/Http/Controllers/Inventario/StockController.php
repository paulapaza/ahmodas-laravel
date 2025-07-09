<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Tienda;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function tiendasConStock(Request $request)
    {
        $productoId = $request->input('producto_id');

        $tiendas = Tienda::all();

        if ($productoId) {
            $producto = Producto::with('tiendas')->find($productoId);
            $tiendas = $tiendas->map(function ($tienda) use ($producto) {
                $tienda->stock = $producto->tiendas
                    ->firstWhere('id', $tienda->id)?->pivot->stock ?? 0;
                return $tienda;
            });
        } else {
            // Si es creación, poner stock en 0
            $tiendas = $tiendas->map(function ($tienda) {
                $tienda->stock = 0;
                return $tienda;
            });
        }

        return response()->json($tiendas);
    }
}
