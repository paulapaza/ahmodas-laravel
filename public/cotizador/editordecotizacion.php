<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.3/b-3.0.1/b-html5-3.0.1/b-print-3.0.1/datatables.min.css" rel="stylesheet">
 
<body>
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1>
                    EDITOR DE COTIZACIONES

                </h1>
            </div>

        </div>

       
            <div class="row mb-3">
                <div class="col-12">
                    <div class="form-group">
                        <label for="cotizacion">Pegue aqui el json</label>
                        <textarea class="form-control" name="cotizacion" id="cotizacion" rows="10"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </div>
        

        <div class="row">
            <div class="col-8 mx-auto">


                <?php
                if (isset($_POST['cotizacion'])) {

                    $productos = json_decode($_POST['cotizacion'], true);
                    

                    //var_dump($cotizacion["cotizacion"]["productos"]);
                    //var_dump($cotizacion);

                    //$productos = $cotizacion["cotizacion"]["productos"];
                    //$productos = $cotizacion["productos"];

                    echo '<table id="example" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Cantidad</th>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($productos as $producto) {
                        echo "<tr>";
                        echo "<td>" . $producto['cantidad'] . "</td>";
                        echo "<td>" . $producto['producto'] . "</td>";
                        echo "<td>" . $producto['precio_unitario'] . "</td>";
                        echo "<td>" . $producto['subtotal'] . "</td>";
                        echo "<td><button class='btn btn-danger remove'>Eliminar</button></td>";
                        echo "</tr>";
                    }
                    echo '
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>Total</th>
                            <th id="total"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>';
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <button class="btn btn-primary export">Exportar</button>
            </div>
        </div>
    </div>
</body>


<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.3/b-3.0.1/b-html5-3.0.1/b-print-3.0.1/datatables.min.js"></script>
<script src="js/dataTables.cellEdit.js"></script>

<script>
    $(document).ready(function() {
        //inicialiozar datable con botones
        table = $('#example').DataTable({
           dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
            ],
            
            columnDefs: [{
                targets: -1,
                className: 'd-print-none',
            }]
        });
        sumarSubtotales();

        function myCallbackFunction(updatedCell, updatedRow, oldValue, idx) {
            //console.log("el nuevo valor para la celda es: " + updatedCell.data());
            //console.log("Los valores para cada celda de esa fila son:" + updatedRow.data());

            let cantidad = updatedRow.data()[0];
            let precio_unitario = updatedRow.data()[2];
            let subtotal = cantidad * precio_unitario;
            updatedRow.data()[3] = subtotal.toFixed(2);
            
           
            console.log(updatedRow[0][0]);
            idx = updatedRow[0][0];
           
            table.cell(idx,3).data(subtotal).draw();

           // console.log("el subtotal es: " + updatedRow.data()[3]);
            console.log("Los valores para cada celda de esa fila son:" + updatedRow.data());
            sumarSubtotales();

        }

        table.MakeCellsEditable({
            "onUpdate": myCallbackFunction,
            "columns": [0,1,2,3],
        });

        function sumarSubtotales() {
            var total = 0;
           // sumar el total de todo el datable
            table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                var data = this.data();
                total += parseFloat(data[3]);
            });
            // imprimir el total dos decimales
            $("#total").text(total.toFixed(2));
        }
        // remover fila de datables
        $('#example tbody').on('click', 'button.remove', function() {
            table.row($(this).parents('tr')).remove().draw();
            sumarSubtotales();
        });
        
        // exportar a toda la tabla  a nuna nueva pagina
        $(document).on('click', 'button.export', function() {
//            var data = table.rows().data().toArray();
            //exportar solo las columnas 1,2,3,4
            var data = table.rows().data().toArray();

            //eliminar la columna de acciones
            data.forEach(function(item) {
                item.pop();
            });
            console.log(data);
           // return;
            //redirect to export.php
            let formData = new FormData();
            formData.append('datatable', JSON.stringify(data));
            // agragar el total
            formData.append('total', $("#total").text());

          //enviar por ajax
            $.ajax({
                url: 'export.php',
                type: 'post',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    window.open('cotizador/print.php', '_blank');
                }
            });
          
                
            
        });


    });
</script>

</html>