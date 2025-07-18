<?php
namespace App\Http\Controllers;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Services\AjaxResponseService;

class ClienteController extends Controller
{
    //index 
    public function index()
    {
        $clientes = Cliente::all();
        return response()->json($clientes);
    }

    //store
    public function store(Request $request)
    {
        $cliente = Cliente::create($request->all());
        return (new AjaxResponseService)->successStore($cliente, 201);
    }
    //update
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());
        return (new AjaxResponseService)->successUpdate($cliente, 200);
    }
    //destroy
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return (new AjaxResponseService)->successDestroy($cliente, 200);
    }
}