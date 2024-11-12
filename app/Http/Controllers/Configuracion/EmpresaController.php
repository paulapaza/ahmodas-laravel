<?php
namespace App\Http\Controllers\Configuracion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Configuracion\CertificadoDigitalRequest;
use App\Http\Requests\Configuracion\CompanyCredentialCPERequest;
use App\Http\Requests\Configuracion\CompanyDatosTributariosRequest;
use App\Models\Configuracion\Empresa;


class EmpresaController extends Controller
{
    public function edit()
    {
        $empresa = DB::table('empresa')->first();

        return view('modules.configuracion.empresa.edit', compact('empresa'));
    }

    public function update_datosComerciales(Request $request)
    {

        $id = 1;
        $nombre_comercial = $request->nombre_comercial;
        $descripcion_comercial = $request->descripcion_comercial;
        $direccion_comercial = $request->direccion_comercial;
        $telefono = $request->telefono;
        $email = $request->email;
        $logo = "150.png";
        $website = $request->website;
        $facturacion_electronica = $request->facturacion_electronica;

        DB::table('empresa')->where('id', $id)->update([
            'nombre_comercial' => $nombre_comercial,
            'descripcion_comercial' => $descripcion_comercial,
            'direccion_comercial' => $direccion_comercial,
            'telefono' => $telefono,
            'email' => $email,
            'logo' => $logo,
            'website' => $website,
            'facturacion_electronica' => $facturacion_electronica,

        ]);

        return response()->json(
            [
                'success' => true,
                'message' => "Los datos comerciales se actualizaron correctamente"
            ],
            200
        );
    }
    public function update_datosTributarios(CompanyDatosTributariosRequest $Companydata)
    {
        //dd($request);   
        $company_id = 1;
        DB::table('empresa')->where('id', $company_id)->update([
            'tipo_documento' => $Companydata->tipo_documento,
            'nro_documento' => $Companydata->nro_documento,
            'razon_social' => $Companydata->razon_social,
            'direccion_fiscal' => $Companydata->direccion_fiscal,
            'pais' => 'Peru',
            'departamento' => $Companydata->departamento,
            'provincia' => $Companydata->provincia,
            'distrito' => $Companydata->distrito,
            'ubigeo' => $Companydata->ubigeo,

        ]);
        return response()->json(
            [
                'success' => true,
                'message' => "Los datos tributarios se actualizaron correctamente"
            ],
            200
        );
    }
    public function update_FacturacionElectronica(CompanyCredentialCPERequest $CompanyCredential)
    {
        $company_id = 1;

        DB::table('empresa')->where('id', $company_id)->update([
            'soap_tipo' => $CompanyCredential->soap_tipo,
            'soap_envio' => $CompanyCredential->soap_envio,
            'soap_usuario' => $CompanyCredential->soap_usuario,
            'soap_clave_usuario' => $CompanyCredential->soap_clave_usuario,

        ]);

        return response()->json(
            [
                'success' => true,
                'message' => "Los datos tributarios se actualizaron correctamente"
            ],
            200
        );
    }
    // certificado digital
    public function update_CertificadoDigital(CertificadoDigitalRequest $request)
    {

        //validar .pem que sea valido en la extencion al menos
        $file = $request->file('certificado_file');
        $extension = $file->getClientOriginalExtension();
        if ($extension != 'pem') {
            return response()->json(
                [
                    'success' => false,
                    'message' => "El archivo no es valido, debe ser un archivo .pem"
                ],
                200
            );
        }


        // guardar el certificado en storage/app/certificado con el nombre certificado.pem
        $file->storeAs('certificado', 'certificado.pem');
        // guardar la clave del certificado

        $id = 1;
        $empresa = Empresa::find($id);
        $empresa->certificado_pass = $request->certificado_pass;
        $empresa->certificado_caducidad = $request->certificado_caducidad;
        $empresa->certificado_path = 'certificado/certificado.pem';
        $empresa->save();




        return response()->json(
            [
                'success' => true,
                'message' => "Los datos del certificado se actualizaron correctamente"
            ],
            200
        );
    }
    public function update_ConsultaDocumentos(Request $request)
    {
        $id = 1;



        DB::table('empresa')->where('id', $id)->update([

            'validador_client_id' => $request->validador_client_id,
            'validador_client_secret' => $request->validador_client_secret,


        ]);



        return response()->json(
            [
                'success' => true,
                'message' => "Los datos tributarios se actualizaron correctamente"
            ],
            200
        );
    }
    public function update_GuiaRemision(Request $request)
    {
        $id = 1;



        DB::table('empresa')->where('id', $id)->update([

            'guia_remision_client_id' => $request->guia_remision_client_id,
            'guia_remision_client_secret' => $request->guia_remision_client_secret,

        ]);



        return response()->json(
            [
                'success' => true,
                'message' => "Los datos tributarios se actualizaron correctamente"
            ],
            200
        );
    }
    public static function getEmpresa($id)
    {
        $empresa = DB::table('empresa')->where('id', $id)->first();

        // dd($empresa);
        if (!$empresa) {
            echo json_encode(
                [
                    'success' => false,
                    'message' => "No se e"
                ]
            );
            exit;
        }

        // empresa -> facturacion_electronica es no
        //dd($empresa->facturacion_electronica);
        if ($empresa->facturacion_electronica == 'NO') {
            echo json_encode(
                [
                    'success' => false,
                    'message' => "La Facturacion Electronica esta desactivada
                    , Active la facturacion electronica para poder emitir comprobantes
                   (Seccion de configuracion de empresa)"
                ]
            );
            exit;
        }
        // comprovar si los datos de facturacion electronica estan completos
        if (
            $empresa->nro_documento == ''
            || $empresa->razon_social == ''
            || $empresa->direccion_fiscal == ''
            || $empresa->departamento == ''
            || $empresa->provincia == ''
            || $empresa->distrito == ''
            || $empresa->ubigeo == ''
        ) {
            echo json_encode(
                [
                    'success' => false,
                    'message' => "Los datos de tributarios de la empresa no estan completos"
                ]
            );
            exit;
        }
        // comprovar si los datos de facturacion electronica estan completos
        if (
            $empresa->soap_tipo == ''
            || $empresa->soap_envio == ''
            || $empresa->soap_usuario == ''
            || $empresa->soap_clave_usuario == ''
        ) {
            echo json_encode(
                [
                    'success' => false,
                    'message' => "Las credenciales facturacion no estan completos"
                ]
            );
            exit;
        }
        // comprovar si los datos de certificado digital estan completos
        if (
            $empresa->certificado_pass == ''
            || $empresa->certificado_caducidad == ''
            || $empresa->certificado_path == ''
        ) {
            echo json_encode(
                [
                    'success' => false,
                    'message' => "Agregue un certificado digital para poder emitir comprobantes(puede ser de prueba)"
                ]
            );
            exit;
        }
        return $empresa;
    }
    //obtener data facturacion electronica

}
