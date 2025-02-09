<x-admin-layout>
    <x-slot name="menu">
        <x-menuCotizador></x-menuCotizador>
    </x-slot>
    <x-slot name="pagetitle">Nueva Cotización</x-slot>
    <div class="row">
        <div class="col-12 col-xl-8 container">
            <div class="card">
                <div class="card-header bg-xgray">
                    <p class="card-title text-xaccent text-bold">Ingrese el json para su revisión</p>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="json-text">Pegue aqui el json</label>
                                <textarea class="form-control" name="cotizacion" id="json-text" rows="10"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="submit" class="btn btn-xsuccess" id="generar-tabla">Generar tabla para su
                                revisión</button>

                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header bg-xgray">
                    <p class="card-title text-xaccent text-bold">Detalle de la cotización</p>
                </div>
                <div class="card-body">
                    <table id="table" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>nro item</th>
                                <th>Cantidad</th>
                                <th>Producto</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>


        </div>
    </div>
    <!-- suma -->
    <div class="row d-flex justify-content-center">
        <div class="col-6 text-center alert alert-danger">
            <h3>Total: <span id="total">0.00</span></h3>
        </div>  
    </div>
    <!-- modal agregar producto -->
    <div class="modal fade" id="modalBuscarProducto" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-xprimary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">Buscar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">

                            <input type="text" class="form-control mb-3" id="product-search-box"
                                placeholder="Buscar Producto">
                        </div>
                        <div class="col-6">

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="all-products">
                                <label class="form-check-label ms-2" for="all-products">
                                    Todos los productos
                                </label>
                            </div>

                            <div id="loading" style="display: none;">
                                Cargando...

                                <i class="fa-solid fa-spinner fa-spin ml-3"></i>
                            </div>
                        </div>
                    </div>

                    <table id="tbl_productos" class="table table-striped">
                        <thead class="bg-secondary">
                            <tr>
                                <th>id</th>
                                <th>Nombre de Producto</th>
                                <th>Cod Barras</th>
                                <th>Precio s/.</th>
                                <th>Edicion</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- modal cantidad -->
   
    <div class="modal fade" id="modalCantidad" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-xprimary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Cantidad</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <input type="number" class="form-control" id="cantidad" placeholder="Cantidad">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnAgregarCantidad">Agregar</button>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

<script>
    $(document).ready(function() {


        var table = $('#table').DataTable({
            responsive: true,
            autoWidth: false,
            destroy: true,
            order: [
                [0, 'desc']
            ],
            //ordering: false,
            "columns": [{
                    "data": "nro_item"
                },
                {
                    "data": "cantidad"
                },
                {
                    "data": "producto"
                },
                {
                    "data": 'precio_unitario',

                },

                {
                    "data": 'subtotal',
                    render: function(data) {
                        return (data !== null && data !== undefined) ? parseFloat(data).toFixed(
                            2) : '0.00';
                    }
                },
                {
                    "data": "null",
                    className: "text-center",
                    "defaultContent": '<button class="btn btn-xs btn-danger btn-delete">Eliminar</button>'
                }
            ],
            columnDefs: [{
                    targets: 0,
                    //visible: false,
                    //className: "nroPadron",
                },
                {
                    targets: 3,
                    orderable: false,
                    className: "text-center",
                    render: function(data, type, row) {

                        return '<input type="number" min="0.1" class="ipt-precio-unitario" style="text-align: center;" "name="name"  value="' +
                            data + '"/>';


                    }
                }


            ],
            layout: {
                topStart: {
                    buttons: [{
                            text: 'Agregar producto',
                            className: '',
                            action: function(e, dt, node, config) {
                                agregarProducto();
                            },
                        }, {
                            text: 'Imprimir Codigos de Barras',
                            className: '',
                            action: function(e, dt, node, config) {
                                imprimirEtiquetas();
                            },
                        },
                        {
                            text: 'Imprimir etiquetas de Precio',
                            className: '',
                            action: function(e, dt, node, config) {
                                imprimirEtiquetasDePrecio();
                            },
                        }
                    ]

                },
            },
        });
        var tabla_productos = $('#tbl_productos').DataTable({
            responsive: true,
            autoWidth: false,
            destroy: true,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    "data": "id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "barcode",
                    type: "num"
                },
                {
                    "data": "list_price",
                    //dos decimales

                    render: function(data, type, row) {
                        return data.toFixed(2);
                    }
                },
                {
                    "data": "id"
                }
            ],
            columnDefs: [{
                    targets: 0,
                    //visible: false,
                    //className: "nroPadron",
                },
                {
                    targets: 4,
                    orderable: false,
                    className: "acciones",
                    render: function(data, type, row) {
                        return '<button class="btn btn-primary btn-sm">Agregar</button>';
                    }
                }
            ],
            language: {
                'search': 'Buscar en el resultados:',
                'lengthMenu': 'Mostrar _MENU_ registros por página',
                'zeroRecords': 'No se encontraron registros',
                'info': 'Mostrando página _PAGE_ de _PAGES_',
                'infoEmpty': 'No hay registros disponibles',
                'infoFiltered': '(filtrado de _MAX_ registros totales)',

            },
        });
        $('#generar-tabla').click(function() {
            var json = $('#json-text').val();
            //cambiar la key al json
            /* [
  {"n":1, "c":1, "p":"Cuaderno Cuadrimax 2x2 Matemática (AMARILLO)", "pu":5.50, "s":5.50},
  {"n":2, "c":1, "p":"Cuaderno Cuadrimax 2x2 Comunicación (AMARILLO)", "pu":5.50, "s":5.50}, */
            // n = nro item, c = cantidad, p = producto, pu = precio unitario, s = subtotal
            json = json.replace(/"n":/g, '"nro_item":');
            json = json.replace(/"c":/g, '"cantidad":');
            json = json.replace(/"p":/g, '"producto":');
            json = json.replace(/"pu":/g, '"precio_unitario":');
            json = json.replace(/"s":/g, '"subtotal":');
            


            var data;

            try {
                data = JSON.parse(json);

                // Si es un objeto con la clave "productos", extraemos ese array
                if (data.productos && Array.isArray(data.productos)) {
                    data = data.productos;
                }

                // Si no es un array en este punto, algo está mal
                if (!Array.isArray(data)) {
                    console.error("Formato JSON no válido");
                    alert("El formato del JSON no es válido. revisa el json");
                    return;
                }

                // Agregar los datos a la tabla
                table.clear().rows.add(data).draw(false);

                //sumar los datos y mostar en un div despues de la tabla
                var total = 0;
                data.forEach(function(item) {
                    total += item.subtotal;
                });
                $('#total').text(total.toFixed(2));


            } catch (error) {
                alert("El formato del JSON no es válido. error: " + error);
            }
        });
        // si se cambia el precio unitario modificar el subtotal
        $('#table tbody').on('change', '.ipt-precio-unitario', function() {
            var data = table.row($(this).parents('tr')).data();
            var precio = $(this).val();
            var cantidad = data.cantidad;
            var subtotal = precio * cantidad;
            data.precio_unitario = precio;
            data.subtotal = subtotal;
            table.row($(this).parents('tr')).data(data).draw(false);
        });
        // agregar producto
        function agregarProducto() {
            $('#modalBuscarProducto').modal('show');
        }
        // buscar producto
        let searchTimeout; // Variable para almacenar el timeout
        $('#product-search-box').keyup(function() {
            clearTimeout(searchTimeout); // Borra el timeout anterior para evitar múltiples llamadas

            let searchString = $(this).val();

            if (searchString !== '' && searchString.length >= 3) {
                searchTimeout = setTimeout(() => { // Inicia un nuevo timeout
                    $.ajax({
                        url: "/odoocpe/barcode/product/search",
                        method: "POST",
                        beforeSend: function() {
                            $("#loading").show();
                        },
                        data: {
                            searchString: searchString,
                            all_products: $('#all-products').is(':checked') ? 1 : 0,
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(data) {
                            if (data.length == 0) {
                                $('#tbl_productos tbody').html(
                                    '<tr><td colspan="5" class="text-center">No se encontraron productos</td></tr>'
                                );
                                return;
                            }
                            tabla_productos.clear().draw();
                            data.forEach(product => {
                                tabla_productos.row.add(product).draw();
                            });
                        },
                        complete: function() {
                            $("#loading").hide();
                        }
                    });
                }, 1200) // Espera 2.5 segundos antes de ejecutar la búsqueda
            } else {
                $('#tbl_productos tbody').html('');
            }
        });

        /***** CARGAR PRODUCTO A LA BANDEJA */
        $('#tbl_productos tbody').on('click', 'tr', function() {
            let row = $(this); // Guarda la referencia a la fila seleccionada

            //cerrar modal
            $('#modalBuscarProducto').modal('hide');

            Swal.fire({
                title: "Submit your Github username",
                input: "text",
                showCancelButton: true,
                confirmButtonText: "ok"
                
                
            }).then((result) => {
                if (result.isConfirmed) {
                    let cantidadIngresada = result.value ||
                        1; // Usa 1 por defecto si no ingresa nada

                    // Obtenemos los datos de la fila seleccionada
                    var data = tabla_productos.row(row).data();

                    // Agregamos la fila con la cantidad ingresada
                    table.row.add({
                        "cantidad": cantidadIngresada,
                        "producto": data.name,
                        "precio_unitario": data.list_price,
                        "subtotal": (cantidadIngresada * data.list_price).toFixed(
                            2), // Calculamos el subtotal
                        "id": data.id
                    }).draw();

                    // Quitamos la fila de la tabla de productos
                    tabla_productos.row(row).remove().draw();

                    // Si ya no hay más productos, cerramos el modal
                    if (tabla_productos.rows().count() == 0) {
                        $('#modalBuscarProducto').modal('hide');
                    }
                }
            });
        });




    });
</script>
