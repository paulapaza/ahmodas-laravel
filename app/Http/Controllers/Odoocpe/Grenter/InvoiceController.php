<?php

namespace App\Http\Controllers\Odoocpe\Grenter;

use Carbon\Carbon;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;

class InvoiceController extends GreenterBaseController
{
    public function __construct()
    {

        parent::__construct();
    }

    public function generarFactura($correlativo, $dataCPE)
    {

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion($dataCPE['cpe']['TipoOperacion'])
            ->setTipoDoc($dataCPE['cpe']['TipoDocumento'])
            ->setSerie($correlativo->serie)
            ->setCorrelativo($correlativo->correlativo)
            ->setFechaEmision(Carbon::now())
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda($dataCPE['cpe']['TipoMoneda'])
            ->setCompany($this->company)
            ->setClient($this->getClient($dataCPE['cliente']))
            ->setMtoOperGravadas($dataCPE['pos_order']['amount_untaxed'])
            ->setMtoIGV($dataCPE['pos_order']['amount_tax'])
            ->setTotalImpuestos($dataCPE['pos_order']['amount_tax'])
            ->setValorVenta($dataCPE['pos_order']['amount_untaxed'])
            ->setSubTotal($dataCPE['pos_order']['amount_total'])
            ->setMtoImpVenta($dataCPE['pos_order']['amount_total']);

        $items = array_map(function ($line) {
           // $valorUnitario = number_format($line['priceSubtotal'] / $line['qty'], 2, '.', '');
            return (new SaleDetail())
                ->setUnidad($line['productUom'])
                ->setCantidad($line['qty'])
                ->setMtoValorUnitario($line['mtoValorUnitario'])
                ->setDescripcion($line['productName'])
                ->setMtoBaseIgv($line['priceSubtotal'])
                ->setPorcentajeIgv(18.00)
                ->setIgv($line['amount_Tax'])
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($line['amount_Tax'])
                ->setMtoValorVenta($line['priceSubtotal'])
                ->setMtoPrecioUnitario($line['priceUnit']);
        }, $dataCPE['pos_lines']);
        //dd($items);
        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($this->CantidadEnLetra($dataCPE['pos_order']['amount_total']));

        $invoice->setDetails($items)->setLegends([$legend]);

        $result = $this->see->send($invoice);

        return $this->processResult($invoice, $result);
    }

    private function processResult($invoice, $result)
    {
        //guardar xml en la carpeta public 

        file_put_contents($invoice->getName() . '.xml', $this->see->getFactory()->getLastXml());

        if (!$result->isSuccess()) {
            return [
                'success' => false,
                'code' => 'Codigo Error: ' . $result->getError()->getCode(),
                'message' => 'Mensaje Error: ' . $result->getError()->getMessage(),
            ];
        }


        file_put_contents('R-' . $invoice->getName() . '.zip', $result->getCdrZip());
        $cdr_base64 = base64_encode($result->getCdrZip());
        $xml = $this->see->getFactory()->getLastXml();
        $xml_base64 = base64_encode($this->see->getFactory()->getLastXml());

        $cdr = $result->getCdrResponse();

        $code = (int)$cdr->getCode();


        return [
            'success' => $code === 0,
            'message' => $code === 0 ? 'ACEPTADA' : 'RECHAZADA',
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes(),
            'cdr_base64' => $cdr_base64,
            'xml_base64' => $xml_base64,
            'xml' => $xml,
        ];
    }
}
