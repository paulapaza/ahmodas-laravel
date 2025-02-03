<?php
require 'vendor/autoload.php';


use Google\Cloud\Vision\V1\ImageAnnotatorClient;

use GeminiAPI\Client;
use GeminiAPI\Enums\MimeType;
use GeminiAPI\Resources\Parts\ImagePart;
use GeminiAPI\Resources\Parts\TextPart;

/* try {
    $imageAnnotatorClient = new ImageAnnotatorClient(['credentials' => 'key.json']);
    $image_path = 'lista-compra.jpeg';
    $imageContent = file_get_contents($image_path);
    $response = $imageAnnotatorClient->textDetection($imageContent);
    $text = $response->getTextAnnotations();
    echo $text[0]->getDescription();

    if ($error = $response->getError()) {
        print('API Error: ' . $error->getMessage() . PHP_EOL);
    }

    $imageAnnotatorClient->close();
} catch(Exception $e) {
    echo $e->getMessage();
} */
// envia a imagem para o gemini;
/* $client = new Client("AIzaSyAMSUx8lxBDTbqApnkr49VkwiwYBTceQnc");
$client = new Client('GEMINI_API_KEY');
$response = $client->geminiPro()->generateContent(
    new TextPart('PHP in less than 100 chars'),
);

print $response->text(); */



$consulta = "que es un perro?";


$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=AIzaSyAMSUx8lxBDTbqApnkr49VkwiwYBTceQnc'; // copiar su API Key de ai.google.dev
       
$datos = [
  'contents' => [
      [
          'parts' => [
              [
                  'text' => $consulta,

              ]
          ]
      ]
  ]
];
$datosJSON = json_encode($datos);


// Configura las opciones de la solicitud cURL
$opciones = array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => '',
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $datosJSON,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
    ),
);

//* Configura las opciones de la solicitud cURL


// Inicializa cURL y configura las opciones
$curl = curl_init();
curl_setopt_array($curl, $opciones);

// Ejecuta la solicitud cURL
$respGemini = curl_exec($curl);
var_dump($respGemini);
exit;

$respuesta = json_decode($respGemini, true);
// Cierra la sesión cURL
curl_close($curl);
// Envia la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode(['mensaje' => $respuesta['candidates'][0]['content']['parts'][0]['text']]);
