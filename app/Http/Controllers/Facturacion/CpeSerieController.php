<?php
namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Http\Services\AjaxResponseService;
use App\Models\Facturacion\CpeSerie;
use Illuminate\Http\Request;

class CpeSerieController extends Controller
{
    //index
    public function index()
    {
        $series = CpeSerie::with('tienda')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json( $series, 200);
    }
    //store
    public function store(Request $request)
    {
        $serie = new CpeSerie();
        $serie->tienda_id = $request->tienda_id;
        $serie->codigo_tipo_comprobante = $request->codigo_tipo_comprobante;
        $serie->serie = $request->serie;
        $serie->correlativo = $request->correlativo;
        $serie->estado = $request->estado;
        $serie->save(); 
        return (new AjaxResponseService)->successStore($serie, 201);

    }
    //update
    public function update(Request $request, $id)
    {
        $serie = CpeSerie::findOrFail($id);
        $serie->tienda_id = $request->tienda_id;
        $serie->codigo_tipo_comprobante = $request->codigo_tipo_comprobante;    
        $serie->serie = $request->serie;
        $serie->correlativo = $request->correlativo;
        $serie->estado = $request->estado;
        $serie->save();
        return (new AjaxResponseService)->successUpdate($serie, 200);
    }
    //destroy
    public function destroy($id)
    {
        $serie = CpeSerie::findOrFail($id);
        $serie->delete();       
        return response()->json(null, 204);
    }
    
    public function getCorrelativo(Request $request)
    {
        $id = $request->id;
        $serie = CpeSerie::where('id', $id)->first();
        return $serie->correlativo;
    }
    public function getSerieCorrelativo(Request $request)
    {
        $tipo_doc = $request->tipo_doc;
        $serie = CpeSerie::where('tipo_documento', $tipo_doc)->get();
        return $serie;
    }
}