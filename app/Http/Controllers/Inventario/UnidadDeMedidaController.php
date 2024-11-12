<?php

namespace App\Http\Controllers\Inventario;

use App\Models\Inventario\UnidadDeMedida;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnidadDeMedidaRequest;
use App\Http\Services\AjaxResponseService;

class UnidadDeMedidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unidadDeMedida = UnidadDeMedida::all();
        return response()->json($unidadDeMedida);
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(UnidadDeMedidaRequest $request)
    {
        $unidadDeMedida = UnidadDeMedida::create($request->all());
        
        return (new AjaxResponseService)->successStore($unidadDeMedida, 201);
    }
   
   
    /**
     * Update the specified resource in storage.
     */
    public function update(UnidadDeMedidaRequest $request, $id)
    {
        $unidadDeMedida = UnidadDeMedida::find($id);
        $unidadDeMedida->update($request->all());
               
        return (new AjaxResponseService)->successUpdate($unidadDeMedida, 200);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $unidadDeMedida = UnidadDeMedida::find($id);
        $unidadDeMedida->delete();
        
        return (new AjaxResponseService)->successDestroy($unidadDeMedida, 200);
    }
}
