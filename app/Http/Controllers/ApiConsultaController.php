<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class ApiConsultaController extends Controller
{
    
    public function consultarDni($dni)
    {
        //endpoint https://api.decolecta.com/v1/reniec/dni?numero=46027897
        //token: sk_9321.3tQqYiVxI6HhMf8fZOZIYUdU6GUcUuuw

        //hacer una consulta a un servicio externo para obtener información del DNI
        $dni = preg_replace('/[^0-9]/', '', $dni); // Limpiar el DNI de caracteres no numéricos
        if (strlen($dni) != 8) {
            return response()->json([
                'success' => false,
                'message' => 'DNI inválido'
            ], 400);
        }
        // hacer la peticion con curl
        $url = "https://api.decolecta.com/v1/reniec/dni?numero={$dni}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer sk_9321.3tQqYiVxI6HhMf8fZOZIYUdU6GUcUuuw',
        ]);
        
        // Configurar timeouts de cURL
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 segundos para conectar
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);        // 10 segundos de ejecución máxima
        
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            Log::error("Error de conexión cURL al consultar DNI {$dni}: " . $curl_error);
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión con el proveedor de DNI.'
            ], 500);
        }

        $data = json_decode($response, true);
        
        if (empty($data) || isset($data['error']) || isset($data['message']) && empty($data['full_name'])) {
            $msg = $data['error'] ?? $data['message'] ?? 'No se encontraron datos para el DNI especificado.';
            Log::warning("Consulta DNI {$dni} fallida o sin resultados: " . $msg);
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function consultarRuc($ruc)
    {
        $ruc = preg_replace('/[^0-9]/', '', $ruc); // Limpiar el RUC de caracteres no numéricos
        if (strlen($ruc) != 11) {
            return response()->json([
                'success' => false,
                'message' => 'RUC inválido'
            ], 400);
        }
        // /v1/sunat/ruc?numero=20601030013
        $url = "https://api.decolecta.com/v1/sunat/ruc?numero={$ruc}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer sk_9321.3tQqYiVxI6HhMf8fZOZIYUdU6GUcUuuw',
        ]);
        
        // Configurar timeouts de cURL
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 segundos para conectar
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);        // 10 segundos de ejecución máxima
        
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            Log::error("Error de conexión cURL al consultar RUC {$ruc}: " . $curl_error);
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión con el proveedor de RUC.'
            ], 500);
        }

        $data = json_decode($response, true);

        if (empty($data) || isset($data['error']) || isset($data['message']) && empty($data['razon_social'])) {
            $msg = $data['error'] ?? $data['message'] ?? 'No se encontraron datos para el RUC especificado.';
            Log::warning("Consulta RUC {$ruc} fallida o sin resultados: " . $msg);
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
