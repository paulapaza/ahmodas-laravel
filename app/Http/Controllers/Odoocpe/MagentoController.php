<?php

namespace App\Http\Controllers\Odoocpe;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MagentoController extends Controller
{
    private $baseUrl;
    private $accessToken;

    public function __construct()
    {
        $this->baseUrl = 'https://magento-192432-0.cloudclusters.net/rest/V1/';
        $this->accessToken = "krzgyfjj2ovrlswcw6hdjp0mvktzkb0g";
    }

    // Obtener un producto por SKU
    public function getProduct($sku)
    {
        $url = $this->baseUrl . "products/{$sku}";
        return response()->json($this->makeCurlRequest($url, 'GET'));
    }

    // Actualizar un producto por SKU
    public function updateProduct(Request $request, $sku)
    {
        $url = $this->baseUrl . "products/{$sku}";
        $data = json_encode(['product' => $request->input('product')]);

        return response()->json($this->makeCurlRequest($url, 'PUT', $data));
    }

    // Método genérico para hacer peticiones con cURL
    private function makeCurlRequest($url, $method, $data = null)
    {
        dd($url, $method, $data); 
        $ch = curl_init();
        $headers = [
            "Authorization: Bearer " . $this->accessToken,
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status_code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
    private function makeCurlRequestX($url, $method, $data = null)
{
    $ch = curl_init();
    $headers = [
        "Authorization: Bearer " . $this->accessToken,
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // ⚠️ Deshabilita la verificación SSL temporalmente (NO usar en producción)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status_code' => $httpCode,
        'response' => json_decode($response, true),
        'curl_error' => $error
    ];
}

}
