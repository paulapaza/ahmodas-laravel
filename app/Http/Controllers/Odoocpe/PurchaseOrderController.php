<?php

namespace App\Http\Controllers\Odoocpe;

use App\Http\Controllers\Controller;
use App\Models\OdooCpe\Odoo;
use Carbon\Carbon;


class PurchaseOrderController extends Controller
{


    public function indexbydate($fechainicio, $fechafin)
    {
        // dd($fechainicio, $fechafin);
        $fechaInicio = Carbon::parse($fechainicio, 'America/Lima')->startOfDay()->setTimezone('UTC')->toDateTimeString();
        $fechaFin = Carbon::parse($fechafin, 'America/Lima')->endOfDay()->setTimezone('UTC')->toDateTimeString();

        $Odoo = new Odoo;
        $ventas = $Odoo->purchaseOrder($fechaInicio, $fechaFin);

        return response()->json($ventas, 200);
    }
    //show
    public function showPurchaseLines($ids)
    {
        $ids = array_map('intval', explode(',', $ids));
        $Odoo = new Odoo;
        $purchase_lines = $Odoo->orderLines($ids);
        //obtener el codigo de barras de los productos

        $product_ids = array_unique(array_map(function ($line) {
            return $line['product_id'][0]; // product_id es un array [id, display_name]
        }, $purchase_lines));

        $productos = array_unique($product_ids);
        $productos = $Odoo->searchProductbyId($productos);
        //$productos = collect($productos)->keyBy('id'); // crea una colección con la clave id
        // añadir la cantidad que trae el purchase_line en el producto
        // Crea un mapa para asociar product_id con barcode
        $barcode_map = array();
        foreach ($productos as $product) {
            $barcode_map[$product['id']] = $product['barcode'];
        }

        // Asocia el barcode con las líneas de pedido
        foreach ($purchase_lines as &$line) {
            $product_id = $line['product_id'][0];
            $line['product_barcode'] = isset($barcode_map[$product_id]) ? $barcode_map[$product_id] : '';
        }
       

        return view('modules.odoocpe.barcode.purchase.show', compact('purchase_lines'));
    }
   
}
