<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Etiquetas</title>
    <style>
        @media print {
            @page {
                /* declaraciones Css */
                size: A4 portrait;
                margin-top: 10mm;
            }
        }

        .hoja_a4 {
            width: 220mm;
            font-family: Arial, Helvetica, sans-serif;
            margin: auto;
        }


        .btn-gradient-slack {
            /* background: linear-gradient(35deg,#3aaf85,#55c79e)!important; */
            color: #fff;
        }

        .contenedor-barcode {
            background: linear-gradient(35deg, #3aaf85, #55c79e) !important;
            color: #fff;
            width: 115px;
            height: 58px;
            float: left;
            padding-top: 0px;
            padding-bottom: 1px;
            margin-bottom: 8px;
            text-align: center;
            margin-right: 2px;
            overflow: hidden;


        }

        .nro-barcode {}

        .barcode {
            transform: scale(1.4) !important;
            width: 115px;

        }

        .new-product {
            border-left: 1px solid #ff0000;

        }

        .contenedor-barcode .title,
        .nro-barcode {
            font-size: 9px;
            text-transform: lowercase;
            font-weight: lighter;
            margin-bottom: 0;
            color: #000000;
            overflow-x: hidden;

        }

        .title {

            width: 98px;
            overflow-y: hidden;
            /* white-space: nowrap;
    height: 15px; */
            margin: auto;
        }
        .color-blue .title { color: blue; }
.color-red .title { color: red; }
.color-green .title { color: green; }
.color-black .title { color: black; }
    </style>
</head>

<body>
    {{--  <div class="hoja_a4">
        
       
           {"nroitem":2,
            "id":3,
            "name":"Arbol navideño 1.80 berry coposo",
            "barcode":"121345",
            "cantidad":1},


    @foreach ($productos as $producto)
        @for ($i = 0; $i < $producto['cantidad']; $i++)
            <div class="contenedor-barcode {{ $i == 0 ? 'new-product' : '' }}">
                <svg class="barcode" jsbarcode-format="code128" jsbarcode-value="{{ $producto['barcode'] }}"
                    jsbarcode-textmargin="0" jsbarcode-margin="2" jsbarcode-width="1" jsbarcode-height="20"
                    jsbarcode-fontSize="12" jsbarcode-displayValue="false" jsbarcode-marginBottom="0"
                    jsbarcode-fontoptions="bold">
                </svg>
                <div class="nro-barcode">{{ $producto['barcode'] }}</div>
                <div class="title">{{ Str::limit($producto['name'], 35) }}</div>
            </div>
        @endfor
    @endforeach
    </div> --}}

    <div class="hoja_a4">
        @php
            $colores = ['blue', 'red', 'green', 'black']; // Lista de colores
            $colorIndex = 0; // Índice inicial
        @endphp
    
        @foreach ($productos as $producto)
            @php
                $colorClass = 'color-' . $colores[$colorIndex]; // Generar clase CSS
                $colorIndex = ($colorIndex + 1) % count($colores); // Alternar color
            @endphp
    
            @for ($i = 0; $i < $producto['cantidad']; $i++)
                <div class="contenedor-barcode {{ $i == 0 ? 'new-product' : '' }} {{ $colorClass }}">
                    <svg class="barcode" jsbarcode-format="code128" jsbarcode-value="{{ $producto['barcode'] }}"
                        jsbarcode-textmargin="0" jsbarcode-margin="2" jsbarcode-width="1" jsbarcode-height="20"
                        jsbarcode-fontSize="12" jsbarcode-displayValue="false" jsbarcode-marginBottom="0"
                        jsbarcode-fontoptions="bold">
                    </svg>
                    <div class="nro-barcode">{{ $producto['barcode'] }}</div>
                    <div class="title">{{ Str::limit($producto['name'], 35) }}</div>
                </div>
            @endfor
        @endforeach
    </div>
       



    <script src="{{ asset('js/JsBarcode.code128.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            JsBarcode(".barcode").init();
        });
    </script>
</body>

</html>
