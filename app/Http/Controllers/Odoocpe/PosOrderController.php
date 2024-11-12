<?php

namespace App\Http\Controllers\Odoocpe;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\SerieCorrelativo;
use App\Models\Facturacion\Sunat\TipoDocumentoIdentidad;
use App\Models\OdooCpe\Odoo;
use Carbon\Carbon;


class PosOrderController extends Controller
{


    public function indexbydate($fechainicio, $fechafin)
    {
        // seteamos la hora a la hoora de odoo , (odoo esta en UTC)
        $fechaInicio = Carbon::parse($fechainicio)->startOfDay()->setTimezone('UTC')->toDateTimeString();
        $fechaFin = Carbon::parse($fechafin)->endOfDay()->setTimezone('UTC')->toDateTimeString();
        
        $Odoo = new Odoo;
        $ventas = $Odoo->ventasOdooIndex($fechaInicio, $fechaFin);

        return response()->json($ventas, 200);
    }
    public function Show($id)
    {

        
        $Odoo = new Odoo;
        $pos_order = $Odoo->pos_order($id);
        if ($pos_order['amount_tax'] == 0) {
            $pos_order['amount_tax'] = 0.18 * $pos_order['amount_total'];
        }
       // dd($pos_order);
        

        $serie = SerieCorrelativo::where('tipo_documento', '03')->get();
        
        $serie[0]['correlativo'] = str_pad($serie[0]['correlativo'], 8, "0", STR_PAD_LEFT);
       



        if ($pos_order['partner_id'] == null) {

            $cliente = [
                'id' => 0,
                'name' => 'Cliente varios',
                'street' => '',
                'city' => '',
                'state_id' => ['', ''],
                'country_id' => '',
                'vat' => '00000000',
                'phone' => '',
                'email' => '',
                'l10n_pe_district' => ['', ''],
                'l10n_latam_identification_type_id' => ['', ''],
                'ubigeo' => '',
            ];
        } else {

            $cliente = $Odoo->partner($pos_order['partner_id'][0]);
        }
        $pos_order_lines = $Odoo->pos_order_line($pos_order['lines']);
        
        /*
        0 => array:9 [
                "id" => 87
                "full_product_name" => "hamburguesa de queso"
                "price_unit" => 5.0
                "product_uom_id" => array:2 [▶]
                "qty" => 2.0
                "price_subtotal" => 8.47
                "price_subtotal_incl" => 10.0
                "tax_ids" => array:1 [▼
                0 => 2
                ]
                "tax_ids_after_fiscal_position" => array:1 [▼
                0 => 2
            ]
        */
        /*
          0 => array:9 [▼
            "id" => 1
            "full_product_name" => "guia anaconda 3m"
            "price_unit" => 3.5
            "product_uom_id" => array:2 [▶]
            "qty" => 2.0
            "price_subtotal" => 7.0
            "price_subtotal_incl" => 7.0
            "tax_ids" => []
            "tax_ids_after_fiscal_position" => []
        ]
        */

       
        // recorremos el array de pos_order_lines para formatear los precios
        foreach ($pos_order_lines as $key => $pos_order_line) {
            // formateamos los precios a 2 decimales
            $pos_order_lines[$key]['price_unit'] = number_format($pos_order_line['price_unit'], 2, '.', '');
            $pos_order_lines[$key]['price_subtotal_incl'] = number_format($pos_order_line['price_subtotal_incl'], 2, '.', '');

            // si el producto no tiene impuesto, le asignamos el impuesto IGV    
            if($pos_order_lines[$key]['tax_ids'] == null){
                $pos_order_lines[$key]['tax_ids'] = [2];
                // calculamos el precio sin impuesto
                $price_subtotal = $pos_order_line['price_subtotal']-($pos_order_line['price_subtotal']*0.18);
                $pos_order_lines[$key]['price_subtotal'] = number_format($price_subtotal, 2, '.', '');
                $pos_order_lines[$key]['tax_ids_after_fiscal_position'] = 2;
            }
        }

        $TipoDocumento = TipoDocumentoIdentidad::all();
        // dd($pos_order_lines);
        return view('modules.odoocpe.pos_order.show', compact('serie', 'pos_order', 'cliente', 'pos_order_lines', 'TipoDocumento'));
    }
}
