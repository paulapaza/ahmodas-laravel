<?php

namespace App\Models\OdooCpe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class Odoo extends Model
{
    use HasFactory;
    private $dbname;
    private $url;
    private $username;
    private $password;

    public function __construct()
    {
        $connection = $this->getOdooConnection();
        
        if ($connection) {
            $this->dbname = $connection['dbname'];
            $this->url = $connection['url'];
            $this->username = $connection['username'];
            $this->password = $connection['password'];
        } else {
            // Manejar el caso en que no se encuentre un registro activo
            throw new \Exception("No hay conexión activa disponible.");
        }
    }

    private function getOdooConnection()
    {

        // Recuperar el primer registro donde estado es 1
        $dbconn = OdooDb::where('estado', 1)->first();
       
        if ($dbconn) {
            return [
                'dbname' => $dbconn->name,
                'url' => $dbconn->url,
                'username' => $dbconn->username,
                'password' => Crypt::decryptString($dbconn->password),
            ];
        } else {
           
            return null;
        }
    }
    private function executeCurlRequest($payload)
    {

        //dd($payload);
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        // Ejecutar la solicitud de autenticación
        $response = curl_exec($ch);
       
        curl_close($ch);
        $response = json_decode($response, true);
        
        /* array:3 [
                "jsonrpc" => "2.0"
                "id" => null
                "error" => array:3 [▼
                    "code" => 200
                    "message" => "Odoo Server Error"
                    "data" => array:5 [▶]
                ]
            ] */
        //dd($response);
        if (isset($response['error'])) {
            return "No se puedo ejecutar la consulta curl";
        }
        //dd($response['result']);
        return $response['result'];
    }

    public function ventasOdooIndex($fecha_inicio, $fecha_fin)
    {
       
        $searchValues = array(
            array('date_order', '>=', $fecha_inicio),
            array('date_order', '<=', $fecha_fin),
            array('note', 'not ilike', 'facturado%') // Filtra los que NO contengan "facturado" al inicio
        );

        $query_sales = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "pos.order",
                    "search_read",
                    $searchValues,
                    array(
                        'id',
                        'name',
                        'date_order',
                        'pos_reference',
                        'amount_total',
                        'user_id',
                        'partner_id',
                        'lines'
                    ),
                )
            ),
        );
        $ventas = $this->executeCurlRequest($query_sales);
       
        // Verificamos si la respuesta tiene resultados
        if (!empty($ventas)) {
            foreach ($ventas as &$venta) {
                // Si existe 'date_order', convertimos la fecha a la zona horaria 'America/Lima'
                if (isset($venta['date_order'])) {
                    $date_utc = $venta['date_order'];

                    // Convertimos la fecha de UTC a 'America/Lima' usando Carbon
                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $date_utc, 'UTC');
                    $date->setTimezone('America/Lima');

                    // Actualizamos la fecha en el array
                    $venta['date_order'] = $date->format('Y-m-d H:i:s');
                }
            }
        }
        return $ventas;
    }
    public function pos_order($id)
    {
        //$id = (int)$id; 
        $pos_order = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "pos.order",
                    "search_read",
                    array(
                        array('id', '=', $id)
                    ),
                    array(
                        'id',
                        'name',
                        'date_order',
                        'pos_reference',
                        'amount_total',
                        'amount_tax',
                        'user_id',
                        'partner_id',
                        'lines'
                    )
                ),
            ),
        );

        // Ejecutamos la solicitud y obtenemos la respuesta
        $pos_order = $this->executeCurlRequest($pos_order);
        
        // Verificamos si hay resultados
        if (!empty($pos_order) && isset($pos_order[0]['date_order'])) {
            // Convertimos la fecha devuelta por Odoo (en UTC) a la zona horaria 'America/Lima'
            $date_utc = $pos_order[0]['date_order'];

            // Creamos el objeto Carbon desde la fecha en UTC
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $date_utc, 'UTC');

            // Ajustamos a la zona horaria 'America/Lima'
            $date->setTimezone('America/Lima');

            // Actualizamos la fecha en la respuesta
            $pos_order[0]['date_order'] = $date->format('Y-m-d H:i:s');
        }
        
        // Retornamos el primer resultado
        return $pos_order[0];
    }

    public function pos_order_line($ids)
    {
         $pos_order_lines = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "pos.order.line",
                    "read",
                    $ids,
                    array(
                        'id',
                        'full_product_name',
                        'price_unit',
                        'product_uom_id',
                        'qty',
                        'price_subtotal',
                        'price_subtotal_incl',
                        'tax_ids',
                        'tax_ids_after_fiscal_position'

                    )
                ),
            ),
        );

        $pos_order_lines = $this->executeCurlRequest($pos_order_lines);

        return $pos_order_lines;
    }

    public function partner($id)
    {

        $id = (int)$id;

        $partner = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "res.partner",
                    "read",
                    array($id),
                    array(
                        'id',
                        'name',
                        'street',       // h-3 tomilla
                        'city',
                        'city_id',      // 35 Arequipa
                        'state_id',     // 1152 Arequipa
                        'country_id',   // 173 peru
                        'vat',
                        'phone',
                        'email',
                        'l10n_pe_district', // 337 cayma
                        'l10n_latam_identification_type_id'
                       
                    )
                ),
            ),
        );

        $partner = $this->executeCurlRequest($partner);
       

        $partner = $partner[0];

        //dd($partner);
        // Mapeo de tipos de identificación 
        //odoo => sunat

        $identificationMap = [
            6 => 0,   // Non-Domiciled Tax Document -> OTROS
            5 => 1,   // dni                        -> LIBRETA ELECTORAL O DNI
            3 => 4,   // Foreign                    -> CARNET DE EXTRANJERIA
            4 => 6,   // ruc                        -> REG. UNICO DE CONTRIBUYENTES

            2 => 7,   // Passport                   -> PASAPORTE
            //13 => 'G', //Safe Passage                ->
            11 => 'E', //TAM Andean Immigration Card -> TAM- Tarjeta Andina de Migración
            12 => 'F', // PTP                        -> Permiso Temporal de Permanencia PTP
             7 => 'A'  // Diplomatic Identity Card   -> Ced. Diplomática de identidad
        ];

        $currentId = $partner['l10n_latam_identification_type_id'][0];

       // sustituimos el codigo de identificacion
        if (isset($identificationMap[$currentId])) {
            $partner['l10n_latam_identification_type_id'][0] = $identificationMap[$currentId];
        }

        // Obtener el código de ubigeo
        /* "l10n_pe_district" => array:2 [▼
                0 => 337
                1 => "Cayma"
            ] */
        if (isset($partner['l10n_pe_district'][0])) {

            /* array:4 [ // app\Models\ApiOdoo.php:299
                "id" => 337
                "code" => "040103"  // este es el ubigeo
                "name" => "Cayma"
                "city_id" => array:2 [▼
                    0 => 35
                    1 => "Arequipa"
                ]
            ] */
            $ubigeo = $this->ubigeo($partner['l10n_pe_district'][0]);
            
            if (isset($ubigeo)) {
                $partner['ubigeo'] = $ubigeo['code'];
               $partner['ciudad'] = $ubigeo['city_id'][1];
            }
        }
        if (isset($partner['state_id'][1])) {
            // Reemplazar "(PE)" por vacío y eliminar espacios en blanco a la derecha
            $partner['state_id'][1] = rtrim(str_replace('(PE)', '', $partner['state_id'][1]));
        }
       // dd($partner);
        return $partner;
    }
    //search partner
    public function search_res_partner($searchString)
    {


        $query_partner = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                //se envio un array con los ids de las lineas de la orden
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "res.partner",
                    "search_read",
                    array(
                        '|',
                        array('name', 'ilike', $searchString),
                        array('vat', 'ilike', $searchString),

                    ),
                    array(
                        'id',
                        'name',
                        'vat',

                    )
                ),
            ),

        );


        // Ejecutar la solicitud de autenticación
        //cambiar el valor de l10n_latam_identification_type_id[0] segun 

        //Sin documento = 0 en odoo = 6 
        //DNI = 1 en odoo = 5,
        //CE = 4
        //Pasaporte = 5
        //Ruc = 6 en odoo = 7, 
        //permiso tenporal = 7, 
        //Otros = 99
        return $this->executeCurlRequest($query_partner);
    }

    public function ubigeo($district_id)
    {
        //dd($district_id);
        $district_id = (int)$district_id;

        $query_ubigeo = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                //se envio un array con los ids de las lineas de la orden
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "l10n_pe.res.city.district",
                    "read",
                    array($district_id),
                    array(
                        'id',
                        'code',
                        'name',
                        'city_id',
                    )
                ),
            ),

        );

        return $this->executeCurlRequest($query_ubigeo)[0];
    }
    public function departamentos()
    {

        $query_departamentos = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "res.country.state",
                    "search_read",
                    array(array('country_id', '=', 173)),
                    array(
                        'id',
                        'code',
                        'name',
                        'country_id',
                    ),
                ),
            ),

        );

        return $this->executeCurlRequest($query_departamentos);
    }
    // ciudades
    public function Provincias($state_id)
    {

        $query_provincias = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "res.city",
                    "search_read",
                    array(array('state_id', '=', $state_id)),
                    array(
                        'id',
                        'name',
                        'state_id',
                        'l10n_pe_code'

                    ),
                ),
            ),

        );



        return $this->executeCurlRequest($query_provincias);
    }
    public function distritos($city_id)
    {


        $city_id = (int)$city_id;
        $query_distritos = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "l10n_pe.res.city.district",
                    "search_read",
                    array(
                        array('city_id', '=', $city_id)
                        //  citY
                    ),
                    array(
                        'id',
                        'code',
                        'name',
                        'city_id',
                    ),
                ),
            ),

        );


        return $this->executeCurlRequest($query_distritos);
    }

    // crear cliente res.partner
    public function create_res_partner($data)
    {


        $query_new_partner = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                //se envio un array con los ids de las lineas de la orden
                'args' => array($this->dbname, 2, $this->password, "res.partner", "create", array($data)),
            ),

        );
        // Configurar la solicitud de autenticación usando cURL


        return $this->executeCurlRequest($query_new_partner);
    }
    // establecer cliente a un pedido

    public  function set_partner_pos_order($idpartner, $idposorder)
    {

        
        $pos_order = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',

                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "pos.order",
                    "write",
                    array($idposorder),
                    array(
                        'partner_id' => $idpartner,
                    )
                ),
            ),

        );

        return $this->executeCurlRequest($pos_order);
        
    }
    public function set_status_note($idposorder, $status)
    {


        $idposorder = (int)$idposorder;

        $query_pos_order = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',

                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "pos.order",
                    "write",
                    array($idposorder),
                    array(
                        'note' => $status,
                    )
                ),
            ),

        );

        //dd($this->executeCurlRequest($query_pos_order));
        return $this->executeCurlRequest($query_pos_order);
    }
    //search product
    public function searchproduct($searchString)
    {
        $query_search_product = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                //se envio un array con los ids de las lineas de la orden
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "product.product",
                    "search_read",
                    array(
                        '|',
                        array('name', 'ilike', $searchString),
                        array('barcode', 'ilike', $searchString),
                    ),
                    array(
                        'id',
                        'name',
                        'barcode',
                        'list_price',
                    )
                ),
            ),

        );

        return $this->executeCurlRequest($query_search_product);
    }
    //purchase order
    public function purchaseOrder($fecha_inicio, $fecha_fin)
    {
       //DD($fecha_inicio, $fecha_fin);
        $searchValues = array(
            array('date_order', '>=', $fecha_inicio),
           array('date_order', '<=', $fecha_fin),
           array('state', 'not ilike', 'cancelado') // Filtra los que NO contengan "cancelado" al inicio
        );

        $query_purchase_order = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "purchase.order",
                    "search_read",
                    $searchValues,
                    array(
                        'id',
                        'name',
                        'date_order',
                        'partner_id',
                        'amount_total',
                        'amount_tax',
                        'state',
                        'order_line'
                    ),
                )
            ),
        );

        $purchase_order = $this->executeCurlRequest($query_purchase_order);
        
        if (!empty($purchase_order)) {
            foreach ($purchase_order as &$order) {
                if (isset($order['date_order'])) {
                    $date_utc = $order['date_order'];
                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $date_utc, 'UTC')->setTimezone('America/Lima');
                    $order['date_order'] = $date->format('Y-m-d H:i:s');
                }
            }
        }
    
        return $purchase_order;
    }

    //purchase order lines
    public function orderLines($ids)
    {
      
        $purchase_order_lines = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "purchase.order.line",
                    "read",
                    $ids,  //se envio un array con los ids de las lineas de la orden, valores INT
                    array(
                        'id',
                        'product_id',
                        'name',
                        'product_qty',
                    )
                ),
            ),
        );

        $purchase_order_lines = $this->executeCurlRequest($purchase_order_lines);

        return $purchase_order_lines;
    }

    // search product by id
    public function searchProductbyId($ids)
    {
        
       
        $query_search_product = array(
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => array(
                'service' => 'object',
                'method' => 'execute',
                //se envio un array con los ids de las lineas de la orden
                'args' => array(
                    $this->dbname,
                    2,
                    $this->password,
                    "product.product",
                    "read",
                    $ids,
                    array(
                        'id',
                        'name',
                        'barcode',
                        'list_price',
                    )
                ),
            ),

        );

        return $this->executeCurlRequest($query_search_product);
    }
}
    