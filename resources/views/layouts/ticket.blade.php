<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>
<style>
    html {
        margin: 2mm;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        padding: 0px;
        font-size: 12px;
    }

    .content {
        width: 230px;
        margin: 0 auto;
    }

    #header {
        margin-bottom: 15px;
    }

    .datos-institucion {
        text-align: center;
        margin-bottom: 10xp;
    }
    .datos-institucion .nombre{
        margin-top: 5px;
        font-weight: bold;
        font-size: 16px;
    }

    .table th {
        border-top: 1px dotted #000;
        border-bottom: 1px dotted #000;
        padding: 4px 0;
    }

    .table td {
        padding: 5px 0;
    }

    .table .col1 {
        width: 30px;
        text-align: center;
        vertical-align: top;
       /*  background-color: lightgray; */
    }

    .table .col2 {
        width: 163px;
       /*  background-color: aqua; */
      
    }

    .table .col3 {
        width: 50px;
        text-align: right;
        vertical-align: top;
       /*  background-color: burlywood */
        
    }

    #notas {
        border: 2px solid #000;
        margin-top: 5px;
        padding: 5px;
        text-align: justify;

    }
    .total {
        border-top: 1px dotted #000;
        padding: 10px 3px 20px 0;
        text-align: right;
    }

    .totalprecio {
        font-weight: bold;
        font-size: 16px;

    }

    #footer {
        text-align: center;
    }

    #footer div {
        padding-top: 10px;
    }
    #datos-recibo {
        margin-top: 10px;
    }
    #datos-recibo td {
        vertical-align: top;
        padding: 0px;

    }

    #datos-recibo td:nth-child(1){
        /* Estilos para la primera columna de la tabla */
        font-weight: bold;
        width: 70px;

    }
   
    /*reporte caja ticket   248px */
    .table .col1-rct{
        width: 160px;
        vertical-align: top;
        text-align: left;
    }

    .table .col2-rct {
        width: 50px;
        text-align: right;
      
    }

    .table .col3-rct {
        width: 38px;
        text-align: right;
        vertical-align: top;
        
    }
</style>

<body>
    <div class="content">
        
        @yield('content')
    </div>

</body>

</html>