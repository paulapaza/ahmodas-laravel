<?php

namespace App\Http\Controllers\Odoocpe;

use App\Http\Controllers\Controller;
use App\Http\Services\AjaxResponseService;
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
    public function show($id)
    {
        // Constante para el divisor del IGV
        $IGV_DIVISOR = 1.18;

        // Obtener todas las series para el tipo de documento '03'
        $series = SerieCorrelativo::where('tipo_documento', '03')->get();

        if ($series->isEmpty()) {
            return AjaxResponseService::error('No se han configurado series y correlativos para el tipo de documento 03');
        }

        // Formatear el correlativo de cada serie
        $series->each(function ($serie) {
            $serie->correlativo = str_pad($serie->correlativo, 8, "0", STR_PAD_LEFT);
        });

        // Obtener orden desde Odoo
        $Odoo = new Odoo;
        $pos_order = $Odoo->pos_order($id);

        // Configurar cliente
        $cliente = $pos_order['partner_id']
            ? $Odoo->partner($pos_order['partner_id'][0])
            : [
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

        // Obtener líneas del pedido
        $pos_order_lines = $Odoo->pos_order_line($pos_order['lines']);

        foreach ($pos_order_lines as &$line) {
            // Configurar impuestos si no existen
            if (empty($line['tax_ids'])) {
                $line['tax_ids'] = [2]; // ID del impuesto IGV
                $line['tax_ids_after_fiscal_position'] = 2;
            }

            // Calcular montos
            $line['price_subtotal'] = floatval(number_format($line['price_subtotal_incl'] / $IGV_DIVISOR, 2, '.', ''));
            $line['amount_tax'] = floatval(number_format($line['price_subtotal_incl'] - $line['price_subtotal'], 2, '.', ''));
        }

        // Calcular totales
        $pos_order['amount_untaxed'] = array_reduce($pos_order_lines, function ($carry, $line) {
            return $carry + $line['price_subtotal'];
        }, 0);

        $pos_order['amount_tax'] = array_reduce($pos_order_lines, function ($carry, $line) {
            return $carry + $line['amount_tax'];
        }, 0);

        $pos_order['amount_total'] = $pos_order['amount_untaxed'] + $pos_order['amount_tax'];

        // Redondear totales
        $pos_order['amount_untaxed'] = floatval(number_format($pos_order['amount_untaxed'], 2, '.', ''));
        $pos_order['amount_tax'] = floatval(number_format($pos_order['amount_tax'], 2, '.', ''));
        $pos_order['amount_total'] = floatval(number_format($pos_order['amount_total'], 2, '.', ''));

        // Obtener documentos de identidad
        $TipoDocumento = TipoDocumentoIdentidad::all();

        // Enviar datos a la vista
        return view('modules.odoocpe.pos_order.show', compact('series', 'pos_order', 'cliente', 'pos_order_lines', 'TipoDocumento'));
    }

    public function ShowOLD($id)
    {
        /*  $serie = SerieCorrelativo::where('tipo_documento', '03')->get();
        $serie[0]['correlativo'] = str_pad($serie[0]['correlativo'], 8, "0", STR_PAD_LEFT); */

        // Obtener serie correlativo solo una vez, mejorando la legibilidad.
        $series = SerieCorrelativo::where('tipo_documento', '03')->get();

        if ($series) {
            $series[0]['correlativo'] = str_pad($series[0]['correlativo'], 8, "0", STR_PAD_LEFT);
        } else {
            return AjaxResponseService::error('No se ha configurado la serie y correlativo para el tipo de documento 03');
        }

        $Odoo = new Odoo;
        $pos_order = $Odoo->pos_order($id);


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
        foreach ($pos_order_lines as $key => $pos_order_line) {
            if ($pos_order_lines[$key]['tax_ids'] == []) {
                $pos_order_lines[$key]['tax_ids'] = [2]; // ID del impuesto IGV
                $pos_order_lines[$key]['tax_ids_after_fiscal_position'] = 2;
            }
            $price_subtotal = $pos_order_line['price_subtotal_incl'] / 1.18;
            $pos_order_lines[$key]['price_subtotal'] = floatval(number_format($price_subtotal, 2, '.', ''));
            $pos_order_lines[$key]['amount_tax'] = floatval(number_format($pos_order_line['price_subtotal_incl'] - $price_subtotal, 2, '.', ''));
        }

        $total_operaciones_gravadas = array_reduce($pos_order_lines, function ($carry, $line) {
            return $carry + $line['price_subtotal'];
        }, 0);

        $pos_order['amount_untaxed'] = floatval(number_format($total_operaciones_gravadas, 2, '.', ''));

        $total_impuestos = array_reduce($pos_order_lines, function ($carry, $line) {
            return $carry + $line['price_subtotal_incl'] - $line['price_subtotal'];
        }, 0);

        $pos_order['amount_tax'] = floatval(number_format($total_impuestos, 2, '.', ''));

        $TipoDocumento = TipoDocumentoIdentidad::all();
        return view('modules.odoocpe.pos_order.show', compact('series', 'pos_order', 'cliente', 'pos_order_lines', 'TipoDocumento'));
    }
}
