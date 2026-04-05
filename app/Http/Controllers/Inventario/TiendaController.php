<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\TiendaRequest;
use App\Models\Inventario\Tienda;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\New_;

class TiendaController extends Controller
{
    public function index()
    {
        $tiendas = Tienda::all();
        return response()->json($tiendas, 200);
    }



    public function store(TiendaRequest $request)
    {
        $tienda = new Tienda();
        $tienda->uid = $request->uid;
        $tienda->nombre = $request->nombre;
        $tienda->direccion = $request->direccion;
        $tienda->telefono = $request->telefono;
        $tienda->estado = $request->estado;
        $tienda->ticket_nota = $request->ticket_nota; // Assuming ticket_nota is a field in the Tienda model
        $tienda->ruta_api_facturacion = $request->ruta_api_facturacion;
        $tienda->token_facturacion = $request->token_facturacion;
        $tienda->mostrar_en_visor = $request->input('mostrar_en_visor', 1);
        $tienda->es_almacen = $request->input('es_almacen', 0);
        $tienda->save();

        if ($tienda->es_almacen == 1) {
            Tienda::where('id', '!=', $tienda->id)->update(['es_almacen' => 0]);
        }

        return response()->json(
            [
                'success' => true,
                'message' => 'Tienda creada correctamente',
                'tienda' => $tienda
            ],
            201
        );
    }
    public function update(Request $request, $id)
    {
        $tienda = Tienda::findOrFail($id);
        $tienda->nombre = $request->nombre;
        $tienda->direccion = $request->direccion;
        $tienda->telefono = $request->telefono;
        $tienda->estado = $request->estado;
        $tienda->ticket_nota = $request->ticket_nota; // Assuming ticket_nota is a field in the Tienda model
        $tienda->ruta_api_facturacion = $request->ruta_api_facturacion;
        $tienda->token_facturacion = $request->token_facturacion;
        $tienda->mostrar_en_visor = $request->input('mostrar_en_visor', 1);
        $tienda->es_almacen = $request->input('es_almacen', 0);
        $tienda->save();

        if ($tienda->es_almacen == 1) {
            Tienda::where('id', '!=', $tienda->id)->update(['es_almacen' => 0]);
        }

        return response()->json(
            [
                'success' => true,
                'message' => 'Tienda actualizada correctamente',
                'tienda' => $tienda
            ],
            200
        );
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

    public function cambiarTienda(Request $request)
    {
        // Validar solo tienda_id
        $request->validate([
            'tienda_id' => 'required|exists:tiendas,id',
        ]);

        // Buscar el usuario
        $user = User::find($request->user_id);

        // Validar que exista
        if (!$user) {
            return response()->json([
                'message' => "Usuario no encontrado."
            ], 404);
        }

        // Actualizar tienda
        $user->tienda_id = $request->tienda_id;
        $user->save();

        return response()->json([
            'message' => "Tienda del usuario actualizada correctamente",
            'user' => $user
        ]);
    }
}
