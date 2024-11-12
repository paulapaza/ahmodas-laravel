<?php
namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\SerieCorrelativo;
use Illuminate\Http\Request;

class SerieCorrelativoController extends Controller
{
    public function getCorrelativo(Request $request)
    {
        $id = $request->id;
        $serie = SerieCorrelativo::where('id', $id)->first();
        $serie['correlativo'] = str_pad($serie['correlativo'], 8, "0", STR_PAD_LEFT);
        return $serie->correlativo;
    }
    public function getSerieCorrelativo(Request $request)
    {
        $tipo_doc = $request->tipo_doc;
        $serie = SerieCorrelativo::where('tipo_documento', $tipo_doc)->get();
        //$serie['correlativo'] = str_pad($serie['correlativo'],8,"0", STR_PAD_LEFT);
        return $serie;
    }
}