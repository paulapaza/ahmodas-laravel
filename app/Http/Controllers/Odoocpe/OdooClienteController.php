<?php

namespace App\Http\Controllers\Odoocpe;

use App\Http\Controllers\Controller;
use App\Models\OdooCpe\Odoo;
use Illuminate\Http\Request;

class OdooClienteController extends Controller
{
   
    public function nuevoCliente(Request $request)
    {

      
        /// en odoo dni = 5, ruc = 6      
        // validar numero de doicumentio segun nro de digitos para ruc  11 digitos, para dni 8 digitos

        if ($request->TipoDocumento == 4) {
            $request->validate([
                'nrodoc' => 'required|digits:11|starts_with:10,20',

            ]);
        } else if ($request->TipoDocumento == 5) {
            $request->validate([
                'nrodoc' => 'required|digits:8',
            ]);
        }
        // validar que el cliente no este registrado en odoo
        $apiOdoo = New Odoo;
        $respuestaodoo = $apiOdoo->search_res_partner($request->nrodoc);
        
        if (array_key_exists('result', $respuestaodoo)) {
            if (count($respuestaodoo['result']) > 0) {
                $respuesta = array(
                    'status' => 'error',
                    'title' => 'Error al crear el cliente',
                    'mensaje' => 'El numero de documento de identidad ya se encuentra registrado en odoo, ingrese el numero de documento correcto o busque el cliente en la lista',
                );
                return json_encode($respuesta);
                            }
        } else if (array_key_exists('error', $respuestaodoo)) {
            $respuesta = array(
                'status' => 'error',
                'title' => 'Error al crear el cliente',
                'mensaje' => $respuestaodoo['error']['data']['name'] . " => " . $respuestaodoo['error']['data']['message'],

            );
            return json_encode($respuesta);
            
        }

        $data = array(
            'name' => $request->razon_social,
            'country_id' => 173, //Peru
            'state_id' => $request->id_departamento, //1149 Arequipa(pe)           
            'city_id' => $request->id_provincia, //35, arequipa
            'l10n_pe_district' => $request->id_distrito, //337
            'street' => $request->direccion,
            'l10n_latam_identification_type_id' => intval($request->TipoDocumento), //5 
            //'l10n_latam_identification_type_id' => $request->TipoDocumento ,//5 
            'vat' => $request->nrodoc, // 12 digitos para ruc valida (10 o 20)
            'phone' => $request->telefono,
            'email' => $request->email,
            'zip' => $request->ubigeo,
        );

        //dd($data);
        $apiOdoo = New Odoo;
        $partner_id = $apiOdoo->create_res_partner($data);
        //retorna el id del cliente creado
        

        if ($partner_id){
            // establecer cliente a pedido
            
            $result = $apiOdoo->set_partner_pos_order($partner_id, intVal($request->pos_order));
            
            $respuesta = array(
                'status' => 'success',
                'title' => 'Cliente creado correctamente',
                'mensaje' => 'Cliente creado correctamente y vinculado al pedido',
                'id_cliente' => $partner_id,
                'res_set_partner' => $result ,

            );

        } else  {
            $respuesta = array(
                'status' => 'error',
                'title' => 'Error al crear el cliente',
                'mensaje' => $respuestaodoo['error']['data']['name'] . " => " . $respuestaodoo['error']['data']['message'],

            );
        }
        return json_encode($respuesta);
    }
    public function buscarCliente(Request $request)
    {
        $apiOdoo = New Odoo;
        $clientes = $apiOdoo->search_res_partner($request->string);
            $count = 1;
            foreach ($clientes as  $cliente) {
                echo "<li id='" . $count . "' idPartner='" . $cliente['id'] . "' tabindex='0' onkeydown='switchFocus(event, " . $count . ")'>" . $cliente['vat'] . " | " . $cliente['name'] . "</li>";
                $count = $count + 1;
            }
        
    }
    public function ObtenerDatosCliente(Request $request)
    {

        $apiOdoo = New Odoo;
        $partnerdata = $apiOdoo->partner($request->id_cliente);
        
        return json_encode($partnerdata);
       
    }
    
}
