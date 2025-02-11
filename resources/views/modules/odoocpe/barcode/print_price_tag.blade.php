<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas de Precio </title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin-top: 10mm;
            }
            
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #6d6d6d;
        }

        .hoja_a4 {
            width: 220mm;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0px auto;
            /*padding: 10mm 20mm;*/
            background-color: #ffffff;
            min-height: 297mm;

        }

        .contenedor-barcode {
            width: 40mm;
            height: 25mm;
            float: left;
            padding-top: 10px;
            padding-bottom: 1px;
            margin-bottom: 8px;
            text-align: center;
            margin-right: 2px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px dotted #6d6d6d;
            justify-content: space-between;

        }

        .price {
            font-size: 60px;
            font-weight: bold;

            margin-bottom: -3px;
        }

        .price .decimals {
            font-size: 35px;
            margin-left: -15px;
        }

        /*  .barcode {
            transform: scale(1.4) !important;
            width: 115px;

        } */

        .title {
            font-size: 10px;
            text-transform: lowercase;
            font-weight: lighter;
            margin-bottom: 0;
            color: #000000;
            overflow-x: hidden;
        }

        .nro-barcode {
            font-size: 12px;
            margin-bottom: 2px;
            font-weight: bold;
            color: #7f7f7f;
            
        }
    </style>
</head>

<body>
    <div class="hoja_a4">


        @foreach ($productos as $producto)
            <div class="contenedor-barcode">
                <div class="title">{{ Str::limit($producto['name'], 60) }}</div>
                {{-- <div class="price">{{ number_format($producto['precio'], 2) }} </div> --}}
                <div class="price">
                    {{ number_format(floor($producto['precio']), 0) }}.
                    <span class="decimals">{{ substr(number_format($producto['precio'], 2), -2) }}</span>
                </div>
                <div class="nro-barcode">{{ $producto['barcode'] }}</div>
                {{--  <svg class="barcode" jsbarcode-format="code128" jsbarcode-value="{{ $producto['barcode'] }}"
                        jsbarcode-textmargin="0" jsbarcode-margin="2" jsbarcode-width="1" jsbarcode-height="7"
                        jsbarcode-fontSize="12" jsbarcode-displayValue="false" jsbarcode-marginBottom="0"
                        jsbarcode-fontoptions="bold">
                    </svg> --}}
            </div>
        @endforeach
    </div>

    {{-- <script src="{{ asset('js/JsBarcode.code128.min.js') }}"></script> --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
        integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <script>
        /* $(document).ready(function() {
                    JsBarcode(".barcode").init();
                }); */
    </script>
</body>

</html>
