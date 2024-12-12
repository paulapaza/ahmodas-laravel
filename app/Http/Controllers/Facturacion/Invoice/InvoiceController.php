<?php
namespace App\Http\Controllers\Facturacion\Invoice;

use App\Http\Controllers\Configuracion\EmpresaController;
use App\Models\Facturacion\SerieCorrelativo;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceController {

    public function store($invoice_data)
    {
        
        /******************************/
        // GUARDAMOS EL INVOICE EN LA DB
        /******************************/
        $emisor = EmpresaController::getEmpresa(1);
        $cliente = $invoice_data['cliente'];
        $cpe = $invoice_data['cpe'];
        $pos_order = $invoice_data['pos_order'];
        $pos_lines = $invoice_data['pos_lines'];

        $serie_corrertivo = SerieCorrelativo::where('id', $cpe['serie_id'])->first();
        
        DB::BeginTransaction();
        $invoice = new Invoice();
            $invoice->idempresa = $emisor->id;
            $invoice->tipocomp = $cpe['TipoDocumento'];
            $invoice->idserie = $cpe['serie_id'];
            $invoice->serie = $serie_corrertivo['serie'];
            $invoice->correlativo = $serie_corrertivo['correlativo'];
            $invoice->fecha_emision = $cpe['fecha_emision'];
            $invoice->hora_emision = (Carbon::now())->format('H:i:s');
            $invoice->codmoneda = $cpe['moneda'];
            $invoice->op_gravadas = $pos_order['amount_total'] - $pos_order['amount_tax'];
            $invoice->op_exoneradas = 0.00;
            $invoice->op_inafectas = 0.00;
            $invoice->igv = $pos_order['amount_tax'];
            $invoice->total = $pos_order['amount_total'];
            $invoice->cliente_Odoo = $pos_order['partner_id'];
            $invoice->cliente_razon_social = $cliente['razon_social'];
            $invoice->cliente_direccion = $cliente['direccion'];
            $invoice->cliente_tipo_doc = $cliente['tipodoc'];
            $invoice->cliente_VAT = $cliente['nrodoc'];
            $invoice->cliente_pais = $cliente['pais'];
            $invoice->cliente_departamento = $cliente['departamento'];
            $invoice->cliente_provincia = $cliente['provincia'];
            $invoice->cliente_distrito = $cliente['distrito'];
            $invoice->forma_pago = $cpe['forma_pago'];
            $invoice->monto_pendiente = $cpe['monto_pendiente'];
            $invoice->estado = self::CREADO;
        $invoice->save();
        $invoiceId = Invoice::latest('id')->first();
        /******************************/
        // GUARDAMOS LA ORDER_LINES EN LA DB 
        /******************************/
        foreach ($pos_lines as $line) {
            if ($line['codigoAfectacion'] == 2) {
                $igv_porcentaje = 18;
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'codigo de afectacion no valido']
                    , 400);
            }
            $invoiceLine = new InvoiceLine();
            $invoiceLine->invoice_id = $invoiceId['id'];
            $invoiceLine->item = $line['positem'];
            $invoiceLine->produc_name = $line['productName'];
            $invoiceLine->cantidad = $line['qty'];
            $invoiceLine->unidad_de_medida = "NIU";
            $invoiceLine->valor_unitario = round($line['priceSubtotal'] / $line['qty'], 2);
            $invoiceLine->precio_unitario = $line['priceUnit'];
            $invoiceLine->igv = $line['priceSubtotalIncl'] - $line['priceSubtotal'];
            $invoiceLine->porcentaje_igv = $igv_porcentaje;
            $invoiceLine->valor_total = $line['priceSubtotal'];
            $invoiceLine->importe_total = $line['priceSubtotalIncl'];
            $invoiceLine->save();
        }
        $this->actualizarSerie($cpe['serie_id']);
       
        DB::commit();
        return $invoiceId;
    }
    public function actualizarSerie($serie_id)
    {
        $serie = SerieCorrelativo::where('id',$serie_id)->first();
        $serie['correlativo'] = $serie['correlativo'] + 1;
        $serie->save();
        
    }
}