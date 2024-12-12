<?php

namespace App\Http\Controllers\Odoocpe;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LetrasController;
use App\Http\Controllers\Api\CpeController;
use App\Http\Controllers\Api\XmlController;
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Odoocpe\Grenter\InvoiceController;
use App\Models\Configuracion\Empresa;
use App\Models\Facturacion\SerieCorrelativo;
use App\Models\Facturacion\Invoice;
use App\Models\Facturacion\InvoiceLine;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class OdooInvoiceController extends Controller
{
    //estado del invoice
    const CREADO = 1;
    const ENVIADO = 2;
    const ACEPTADO = 3;
    const RECHAZADO = 4;
    const ANULADO = 5;

    public function index()
    {
        //paginar los invoices
        //$invoices = Invoice::paginate(2);
        $invoices = Invoice::all();
        return view('facturacion/documentosElectronicos', compact('invoices'));
    }
    public function create(Request $request)
    {
        if ($request['cpe']['TipoDocumento'] == '03') { // boleta
            // $emisor = Empresa::where('id', 1)->first();
            $new_invoice = $this->storeCPE($request->all());
            return json_encode([
                'success' => true,
                'message' => 'Boleta creada correctamente',
                'descripcion' => 'La Boleta numero ' . $new_invoice->serie .
                    '-' . $new_invoice->correlativo . ', se ha creado guardado en la base de datos',
                'notas' => 'La boleta no se ha enviado a la sunat, tienes que  enviarla en un resumen diario',
                'invoice' => $new_invoice
            ]);
        }

        if ($request['cpe']['TipoDocumento'] == '01') { //fa

            $serie_numero = SerieCorrelativo::where('id', $request['cpe']['serie_id'])->first();
            $new_invoice = new InvoiceController();
            $sunat_response = $new_invoice->generarFactura($serie_numero, $request->all());
            
            if ($sunat_response['success'] == true) {
                $invoice = $this->storeCPE($request->all(),$sunat_response);

                $respuesta['success'] = true;
                $respuesta['title'] = "Aceptado por la SUNAT";
                $respuesta['invoice'] = $invoice;
                $respuesta['descripcion'] = $sunat_response['description'];
                $respuesta['notas'] = 'notas';
                
            } else {
                $respuesta['success'] = false;
                $respuesta['message'] = $sunat_response['description'];
            }
        }
        /*  if ($request['cpe']['TipoDocumento'] == '07') { //nc
            $emisor = EmpresaController::getEmpresa(1);
            $serie_numero = Serie::where('id', $request['cpe']['serie_id'])->first();
            $new_invoice = new GrenterController();
            $response = $new_invoice->generarNotaCredito($serie_numero, $request->all());
        }
        if ($request['cpe']['TipoDocumento'] == '08') { //nd
            $emisor = EmpresaController::getEmpresa(1);
            $serie_numero = Serie::where('id', $request['cpe']['serie_id'])->first();
            $new_invoice = new GrenterController();
            $response = $new_invoice->generarNotaDebito($serie_numero, $request->all());
        } */

        return json_encode($respuesta);
            
    }


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
    public function actualizarSerie($serie_id)
    {
        $serie = SerieCorrelativo::where('id', $serie_id)->first();
        $serie['correlativo'] = $serie['correlativo'] + 1;
        $serie->save();
    }
    /*  function CantidadEnLetra($tyCantidad)
    {
        $enLetras = new LetrasController();
        return $enLetras->ValorEnLetras($tyCantidad, "SOLES");
    } */

    public function invoicePdf($tipo, $id)
    {

        $empresa = Empresa::find('1');
        $invoice = Invoice::find($id);
        // dd($invoice);
        $total_letras = new LetrasController();

        $invoice->total_letras = $total_letras->ValorEnLetras($invoice->total, "Soles");
        $qr_string = $empresa->ruc . '|' . $invoice->tipo_documento . '|' . $invoice->serie . '|' . $invoice->correlativo . '|' . $invoice->igv . '|' . $invoice->total . '|' . $invoice->fecha_emision . '|' . $invoice->cliente_tipo_doc . '|' . $invoice->cliente_vat;
        $qrcode = base64_encode(QrCode::format('svg')->size(100)->errorCorrection('H')->generate($qr_string));


        $invoiceLines = InvoiceLine::where('invoice_id', $id)->get();

        if ($tipo == 'ticket') {
            $pdf = PDF::loadView('print/invoiceticket', compact('invoice', 'empresa', 'invoiceLines', 'qrcode'));
        } else {
            $pdf = PDF::loadView('print/invoiceA4', compact('invoice', 'empresa', 'invoiceLines', 'qrcode'));
        }

        return $pdf->stream('invoice.pdf', array('Attachment' => 0));
    }
    private function storeCPE($invoice_data)
    {

        /*  array:5 [ // app\Http\Controllers\Odoocpe\OdooInvoiceController.php:138
            "cliente" => array:10 [
              "id" => "22"
              "tipodoc" => "6"
              "nrodoc" => "10451133409"
              "razon_social" => "Taissa Christhell Herrera Ventura"
              "direccion" => "C.C.siglo XX - A. Avelino Caceres. J.Bustamante, psj. 10 , int 780-871"
              "departamento" => "Arequipa"
              "provincia" => "Arequipa"
              "distrito" => "José Luis Bustamante Y Rivero"
              "ubigeo" => "040129"
              "pais" => "PE"
            ]
            "cpe" => array:8 [
              "TipoDocumento" => "01"
              "serie_id" => "1"
              "correlativo" => "00000001"
              "fecha_emision" => "2024-11-21"
              "forma_pago" => "Contado"
              "monto_pendiente" => "0"
              "TipoOperacion" => "0101"
              "TipoMoneda" => "PEN"
            ]
            "pos_order" => array:4 [
              "pos_order" => "1155"
              "partner_id" => "0"
              "amount_tax" => "21.36"
              "amount_total" => "140"
            ]
            "pos_lines" => array:2 [
              0 => array:10 [
                "positem" => "1"
                "pos_order_line" => "2015"
                "productName" => "ramo flor durazno x30 JO"
                "qty" => "1"
                "priceUnit" => "50.00"
                "codigoAfectacion" => "1"
                "priceSubtotal" => "42.37"
                "priceSubtotalIncl" => "50.00"
                "productUom" => "NIU"
                "mtoValorUnitario" => "42.37"
              ]
              1 => array:10 [
                "positem" => "2"
                "pos_order_line" => "2016"
                "productName" => "base mimbre pie madera a/b/c 4l zho15-5m AM-25-50 AM-25-49"
                "qty" => "1"
                "priceUnit" => "90.00"
                "codigoAfectacion" => "1"
                "priceSubtotal" => "76.27"
                "priceSubtotalIncl" => "90.00"
                "productUom" => "NIU"
                "mtoValorUnitario" => "76.27"
              ]
            ]
            "_token" => "rR2MpP0sL3vLTfPdEfoojdTjNoTDkpxHH2bbblma"
          ] */
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
        $invoice->codmoneda = $cpe['TipoMoneda'];
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

            /* if ($line['codigoAfectacion'] == 2) {
                $igv_porcentaje = 18;
            }else{
                $response['success'] = false;
                $response['message'] = 'Error en la creacion de la factura, codigo de afectacion no valido<br>
                                     codigo de afectacion debe ser 2, pero es: ' . $line['codigoAfectacion'];
                return $response;
            }
             */
            $line['codigoAfectacion'] == 2;
            $igv_porcentaje = 18;

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
}
