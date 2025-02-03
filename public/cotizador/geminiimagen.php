<?php
// Realiza alguna acción con los datos (aquí puedes agregar tu lógica de procesamiento)
 $consulta = "
extre el texto de la imagen procesalo y devuelveme el texto de la imagen.
 ";
 // Obtiene la imagen y la guarda en una ubicación temporal

/*  $imagenTemporal = tempnam(sys_get_temp_dir(), 'imagen_');
 move_uploaded_file($_FILES['imagen']['tmp_name'], $imagenTemporal); */

$imagenTemporal = 'imagen.jpg';
$imagenTemporal = 'lista-compra.jpeg';

 $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent?key=AIzaSyAMSUx8lxBDTbqApnkr49VkwiwYBTceQnc'; // copiar su API Key de ai.google.dev

 $datos = [
     'contents' => [
         [
             'parts' => [
                 [
                     'text' => $consulta
                 ],
                 [
                     'inline_data' => [
                         'mime_type' => 'image/jpeg',
                         'data' => base64_encode(file_get_contents($imagenTemporal))
                     ]
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

// Inicializa cURL y configura las opciones
$curl = curl_init();
curl_setopt_array($curl, $opciones);

// Ejecuta la solicitud cURL
$respGemini = curl_exec($curl);



// Transformamos a formato JSON
$respuesta = json_decode($respGemini, true);

// Cierra la sesión cURL
curl_close($curl);

// Elimina el archivo temporal
//unlink($imagenTemporal);
header('Content-Type: application/json');
echo $respuesta['candidates'][0]['content']['parts'][0]['text'];
// Envia la respuesta en formato JSON
//echo json_encode(['mensaje' => $respuesta['candidates'][0]['content']['parts'][0]['text']]);