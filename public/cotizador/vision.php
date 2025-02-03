<?php
require 'vendor/autoload.php';


use Google\Cloud\Vision\V1\ImageAnnotatorClient;



try {
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
}

