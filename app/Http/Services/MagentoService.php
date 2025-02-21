<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MagentoService
{
    protected $client;
    protected $baseUrl;
    protected $accessToken;

    public function __construct()
    {
        $this->baseUrl = env('MAGENTO_BASE_URL'); // URL de tu Magento
        $this->accessToken = env('MAGENTO_ACCESS_TOKEN'); // Token de acceso

        $this->client = new Client([
            'base_uri' => $this->baseUrl . '/rest/V1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    public function getProduct($sku)
    {
        try {
            $response = $this->client->get("products/{$sku}");
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return $this->handleException($e);
        }
    }

    public function updateProduct($sku, $data)
    {
        try {
            $response = $this->client->put("products/{$sku}", [
                'json' => ['product' => $data]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return $this->handleException($e);
        }
    }

    private function handleException($e)
    {
        if ($e->hasResponse()) {
            return json_decode($e->getResponse()->getBody(), true);
        }
        return ['error' => $e->getMessage()];
    }
}
