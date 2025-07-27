<?php

namespace App\Http\Controllers;

class ApiConsultaController extends Controller
{
    
    public function consultarDni($dni)
    {
        //endpoint https://api.decolecta.com/v1/reniec/dni?numero=46027897
        //token: sk_9321.3tQqYiVxI6HhMf8fZOZIYUdU6GUcUuuw

        //hacer una consulta a un servicio externo para obtener información del DNI
        $dni = preg_replace('/[^0-9]/', '', $dni); // Limpiar el DNI de caracteres no numéricos
        if (strlen($dni) != 8) {
            return response()->json(['error' => 'DNI inválido'], 400);
        }
        // hacer la peticion con curl
        $url = "https://api.decolecta.com/v1/reniec/dni?numero={$dni}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer sk_9321.3tQqYiVxI6HhMf8fZOZIYUdU6GUcUuuw',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function consultarRuc($ruc)
    {
        if (strlen($ruc) != 11) {
            return response()->json(['error' => 'RUC inválido'], 400);
        }
        // /v1/sunat/ruc?numero=20601030013
        $url = "https://api.decolecta.com/v1/sunat/ruc?numero={$ruc}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer sk_9321.3tQqYiVxI6HhMf8fZOZIYUdU6GUcUuuw',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
