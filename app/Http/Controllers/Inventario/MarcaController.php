<?php

namespace App\Http\Controllers\Inventario;

use App\Models\Inventario\Marca;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarcaRequest;
use App\Services\AjaxResponseService;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $marcas = Marca::all();
        return response()->json($marcas, 200);
    }

   
    public function store(MarcaRequest $request)
    {
        $marca = Marca::create($request->all());
        return (new AjaxResponseService)->successStore($marca, 201);
    }
    
    
   
    public function update(MarcaRequest $request, $id)
    {
        $marca = Marca::find($id);
        $marca->update($request->all());
        return (new AjaxResponseService)->successUpdate($marca, 200);
    }

   
    public function destroy($id)
    {
        $marca = Marca::find($id);
        $marca->delete();
        return (new AjaxResponseService)->successDestroy($marca, 200);
    }
}
