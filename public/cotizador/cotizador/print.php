<?php

session_start();

$productos = $_SESSION['datatable'];
// convertir a numero flotante y formatear a 2 decimales
foreach ($productos as $key => $producto) {
    $productos[$key][2] = number_format(floatval($producto[2]), 2);
    $productos[$key][3] = number_format(floatval($producto[3]), 2);
}




// imprimir la lista de productos en formato ticket
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<style>
    table {
        font-family: Verdana, Geneva, Tahoma, sans-serif;
        font-size: 12px;
    }

    table td {
        padding: 0px 5px !important;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <h1>
                    COTIZACION
                    <?php 
                    // imprime un numero aleatori de 5 digitos
                    echo rand(10000, 99999);
                    ?>
                </h1>
                <h3>Libreria Philco</h3>
                <div>Telefono: 9784783812</div>
                <div>Dirección: Av. Los Incas 1234</div>
                <div>Fecha: <?php echo date('d/m/Y'); ?></div>
            </div>
            <div class="mx-auto">
                <table class="table table-striped compact" style="width:100%">
                    <thead class="thead-inverse">
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#</td>
                            <td>Cant.</td>
                            <td>Producto</td>
                            <td>Precio Unitario</td>
                            <td>Subtotal</td>
                        </tr>
                        <?php
                        $numerodeproducto = 1;
                       
                        foreach ($productos as $producto) {

                            $precio = $producto['2'];
                            $subtotal = $producto['3'];
                            echo "<tr>";
                            echo "<td>" . $numerodeproducto++ . "</td>";
                            echo "<td>" . $producto['0'] . "</td>";
                            echo "<td>" . $producto['1'] . "</td>";
                            echo "<td>" . $precio . "</td>";
                            echo "<td>" . $subtotal . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="precio-total text-end">
                <h4>Total: S/. <?php echo $_SESSION['total']; ?></h4>
            </div>
            
            <div class="notas">
                <h4>Notas importantes:</h4>
                
                <ul>
                    <li>La cotizacion ha sido generada por inteligencia artificial.</li>
                    <li>Si tiene alguna duda con algun precio que puede parecer anormal, por favor consultenos</li>
                    <li>Se han escogido los producto tomando en cuenta calidad y mejor precio</li>
                    <li>Las marcas con las que trabajamos son Layconsa, faber, vikingo, kp, vinifan etc</li>
                    <li>Los precios son referenciales y pueden variar segun la disponibilidad del prodcuto</li>
                    <li>La cotizacion es valida por 4 dias</li>
                    <li>Puede escribirnos a nuestro whashapp 9784783812 para cualquier consulta</li>
                </ul>
                
            </div>

        </div>
    </div>



</body>

</html>