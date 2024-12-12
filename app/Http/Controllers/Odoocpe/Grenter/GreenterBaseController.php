<?php

namespace App\Http\Controllers\Odoocpe\Grenter;

use App\Models\Configuracion\Empresa;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

class GreenterBaseController
{
    protected $company;
    protected $see;

    public function __construct()
    {
        $empresa = Empresa::where('id', 1)->first();
        $this->setCompany($empresa);
        $this->see = $this->getSee($empresa);
    }

    private function setCompany($empresa)
    {
        $address = (new Address())
            ->setUbigueo($empresa->ubigeo)
            ->setDepartamento($empresa->departamento)
            ->setProvincia($empresa->provincia)
            ->setDistrito($empresa->distrito)
            ->setUrbanizacion('-')
            ->setDireccion($empresa->direccion_fiscal)
            ->setCodLocal('0000');

        $this->company = (new Company())
            ->setRuc($empresa->nro_documento)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial)
            ->setAddress($address);
    }

    protected function getSee($empresa)
    {
        $see = new See();
        $see->setCertificate(file_get_contents(storage_path('app/private/' . $empresa->certificado_path)));
        $see->setService($empresa->modo_produccion ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        $see->setClaveSOL($empresa->nro_documento, $empresa->soap_usuario, $empresa->soap_clave_usuario);
        return $see;
    }
    protected function getClient(array $dataCliente): Client
    {
        return (new Client())
            ->setTipoDoc($dataCliente['tipodoc'])
            ->setNumDoc($dataCliente['nrodoc'])
            ->setRznSocial($dataCliente['razon_social']);
    }
    function CantidadEnLetra($tyCantidad)
    {
        $enLetras = new CantidadenLetrasController();
        return $enLetras->ValorEnLetras($tyCantidad, "SOLES");
    }
}
