<?php
$consulta = "formatea el texto, es una lista de compras , pon saltos de linea: GUIAS: INSTITUCIÓN EDUCATIVA PRIVADA RAYITOS DE SOL URBANIZACION PUERTA VERDE E-10 JOSE LUIS BUSTAMANTE Y RIVERO 01 pack de Guías RAYITOS DE SOL 01agenda rayitos de sol CUADERNOS: Teléfono: 992184754 INICIAL 2 años Lista De Útiles Escolares 01 Cuaderno Cuadrimax 2x2 Matemática (AMARILLO) 01 Cuaderno Cuadrimax 2x2 Comunicación (AMARILLO) 01 Cuaderno Cuadrimax 2x2 Estimulación de Lenguaje (ANARANJADO) 01 Sobre De Plástico T/ Oficio Con Broche Color Lila (niñas) y color azul (niños). 01 Folder Portatodo Con Liga 02 SKETCHBOOK sin anillar PAPELERÍA: 01 pliego de papel platino 04 Pliegos De Papel Crepe(rojo, amarillo, verde,azu) 01 Pliego De Papel Crepe Con Diseño 02 Pliegos De Papel De Regalo con dibujos grandes 04 Pliegos De Papelógrafos(2 Blancos, 2 Cuadriculados) 50 Hojas Arco iris colores claros. 50 Hojas Arco iris colores fosforescentes. 05 Papel kraft 01 Paquete de Hojas Bond - A4 50 Hojas Bond - A3 CARTULINAS: 06 Pliegos De Cartulinas (02 blancas, 02 negras y 2 de color) 02 Pliego De Cartulina Dúplex 02 Pliego De Cartulina Corrugada 01 Block Todo papel (Cartulina De Colores.) 01 Blook lustre 02 Pliegos De Cartulina Escarchada. MATERIALES DE TRABAJO: 01 Metro De Microporoso ROJO 01 Metro De Microporoso Escarchado 01 Metro De Corrospum color MARRON 01 Cartuchera De Plástico Rectangular Color Lila (niñas) y azul (niños) Con Nombre. 01 Punzón 01 Mata mosca pequeño 02 Lápices Jumbo Triangular grueso. 01 Borradores Grandes Blancos 01 Tajador Jumbo con depósito 01 Caja De Colores Triangulares Jumbo X12 Unid. 01 Caja De Crayolas Jumbo X12 Unid. 01 Estuche de Plumones Gruesos x12und 02 Cajas de plastilina jumbo neon x 12 03 Fine Pen (Azul, Rojo Y Negro) 02 Plumones Indelebles (Delgado y Grueso) 02 Plumón de pizarra 01tabla de punzar doble uso pizarra y fomix 01 Tijera mango naranja Con Nombre 02 Cintas De Embalaje - 02 Limpia Tipos 02 Cinta de Masking Tape gruesa-delgado 02 Frascos de silicona líquida. 01 Frasco de goma con aplicador 01 Cinta de Masking Tape de psicomotricidad 10 Barras De Silicona 05 Metros De Blonda Recogida colores - para 01 Plancha De Ojitos Adhesivos De Animales (diferentes tamaños) 01Frasco De Tempera con aplicador De Colores ½ Metro de Yute de color 01 cajita de hisopos ½ Docena de cheniles de colores 01 Bolsa De Palitos Bajalengua 01 Esponja Rectangular 01 Rodillo Pequeño con Forma 01 Pincel Grueso-01 stickers para nombres 01 Plancha de Teknopor forrado T/Oficio 20 Globos N° 9-20 pali globos 01 Mandil de minichef JUEGOS DIDÁCTICOS y MOTRICIDAD FINA: 01 Juego acorde a la edad (Legos) 01 Cubo de Encajes 01 Rompecabezas de encaje de 10 pzas. 01 Instrumento Musical (pandereta, maracas, par de claves, xilófono, tambor, triangulo) 06 play-doh 20 cuentas grandes de colores con su taper 01 Par de Pasadores con zapato 02 Docena de Ganchos De Plástico 01 Títere de trapo 01 Pelota de Trapo PlásticoNDER 12 ojitos movibles de diferentes tamaños 02 Pañuelos de colores tamaño 40*40 DESCARTABLES: 20 Vasos (10 Teknopor - 10 plástico) 10 Cucharitas - 10 tenedores De Plástico 20 Platos Descartables (pequeños-grandes) 01 Paquete de sorbetes 01 Paquete De Bolsas Celofán (grande) ASEO: 01 Muda completa en su respectiva bolsa de tela con nombre 02 percheros ANO 01 Colonia Mediana - 01 Peine-01 cepillo_pasta dental _vaso 01Individual 01 Jabón liquido.- 03 Bolsas De Pañitos húmedos -01 Frasco De Poett De 1 L. (aroma lavanda) 03 Rollos De Papel Toalla y 06 Rollos de Papel Higiénico doble hoja ";

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=AIzaSyAMSUx8lxBDTbqApnkr49VkwiwYBTceQnc'; // copiar su API Key de ai.google.dev

$datos = [
  'contents' => [
      [
          'parts' => [
              [
                  'text' => $consulta
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

$respuesta = json_decode($respGemini,true);
// Cierra la sesión cURL
curl_close($curl);

// Envia la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode(['mensaje' => $respuesta['candidates'][0]['content']['parts'][0]['text'] ]);
?>