<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\New_;

class TiendaController extends Controller
{
    public function index()
    {
        $tiendas = Tienda::all();
        return response()->json($tiendas, 200);
    }

  

    public function store(Request $request)
    {
        $tienda = new Tienda();
        $tienda->nombre = $request->nombre;
        $tienda->direccion = $request->direccion;
        $tienda->telefono = $request->telefono;
        $tienda->estado = $request->estado;
        $tienda->ruta_api_facturacion = $request->ruta_api_facturacion;
        $tienda->token_facturacion = $request->token_facturacion;
        $tienda->save();
        return response()->json(
            [
                'success' => true,
                'message' => 'Tienda creada correctamente',
                'tienda' => $tienda
            ], 201
        );
    }
    public function update(Request $request, $id)
    {
        $tienda = Tienda::findOrFail($id);
        $tienda->nombre = $request->nombre;
        $tienda->direccion = $request->direccion;
        $tienda->telefono = $request->telefono;
        $tienda->estado = $request->estado;
        $tienda->ruta_api_facturacion = $request->ruta_api_facturacion;
        $tienda->token_facturacion = $request->token_facturacion;
        $tienda->save();
        return response()->json([
            'success' => true,
            'message' => 'Tienda actualizada correctamente',
            'tienda' => $tienda
        ]
        , 200);
    
    }
    //destroy
    public function destroy($id)
    {
        $tienda = Tienda::findOrFail($id);
        $tienda->delete();
        return response()->json([
            'success' => true,
            'message' => 'Tienda eliminada correctamente'
        ], 200);
    }
}