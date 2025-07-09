<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TiendaController extends Controller
{
    public function index()
    {
        $tiendas = DB::table('tiendas')
            ->select('id', 'nombre', 'direccion', 'estado')
            ->get();

        return response()->json($tiendas, 200);
    }

    public function show($id)
    {
        $tienda = DB::table('tiendas')->find($id);

        return response()->json($tienda, 200);
    }

    public function store(Request $request)
    {
        $tienda = new \App\Models\Inventario\Tienda();
        $tienda->fill($request->all());
        $tienda->save();

        return response()->json($tienda, 201);
    }
}