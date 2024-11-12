<x-admin-layout>
    <x-slot name="menu">
        <x-menuOdoocpe />
    </x-slot>
    <x-slot name="pagetitle">Cotizaciones Odoo</x-slot>
    <x-slot name="titulo">Cotizaciones Odoo</x-slot>


    <div class="row justify-content-center pt-2">


        <div class="col-12">

            <table id="tbl_bandeja" class="table table-striped">
                <thead class="bg-secondary">
                    <tr>
                        <th>Nro</th>
                        <th>id</th>
                        <th>Nombre de Producto</th>
                        <th>Cod Barras</th>
                        <th>Cantidad</th>
                        <th>Edicion</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @php
                        $nroitem = 1;
                    @endphp
                    @foreach ($purchase_lines as $line)
                        <tr>
                            <td>{{ $nroitem }}</td>
                            <td>{{ $line['product_id'][0] }}</td>
                            <td>{{ $line['name'] }}</td>
                            <td>{{ $line['product_barcode'] }}</td>
                            <td>{{ $line['product_qty'] }}</td>
                            <td>
                                <span class="btnQuitar px-1 botonodooico" style="cursor: pointer;" title="Quitar de la lista">
                                    <i class="fa-solid fa-trash"></i>
                                </span>
                            </td>
                           
                        </tr>
                        @php
                            $nroitem++;
                        @endphp
                    
                    @endforeach


                </tbody>
            </table>
            <div>
                <span>Etiquetas totales:</span> <span id="TotalEtiquetas"></span> | Número de Hojas a imprimir <span
                    id="TotalHojas">1</span><br>
                <span>N°Etiquetas x hoja :</span> 140 | N° Etiquetas Para completar hoja: <span id="EtiquetasFaltantes">
                </span>
            </div>

        </div>
    </div>

   //modal
    <div class="modal fade" id="modalBuscarProducto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-xprimary text-white">
                    <h5 class="modal-title" id="exampleModalLabel">Buscar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-8">
            
                            <input type="text" class="form-control mb-3" id="product-search-box" placeholder="Buscar Producto">
                        </div>
                        <div class="col-4">
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
                                <th>Precio</th>
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
    <input type="hidden" id="iptcsrf" value="{{ csrf_token() }}">




</x-admin-layout>
<script>
    $(document).ready(function() {
        var accion;
        $('form').submit(function(e) {
            e.preventDefault();
            // o return false;
        });
        let csrf = $("#iptcsrf").val();

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
                    "data": "barcode"
                },
                {
                    "data": "list_price"
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


        var table_bandeja = $('#tbl_bandeja').DataTable({
                responsive: true,
                autoWidth: false,
                destroy: true,
                order: [
                    [0, 'desc']
                ],
                //ordering: false,



                language: {
                    url: '//cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json'
                },
                "columns": [{
                        "data": "nroitem"
                    },
                    {
                        "data": "id"
                    },
                    {
                        "data": "name"
                    },

                    {
                        "data": "barcode"
                    },
                    {
                        "data": "cantidad"
                    },
                    {
                        "data": "acciones"
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
                        className: "text-center",
                        render: function(data, type, row) {

                            return '<input type="number" min="1" class="iptCantidad" style="text-align: center;" "name="name"  value="' +
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
                                buscarProducto();
                            },
                        }, {
                            text: 'Imprimir etiquetas',
                            className: '',
                            action: function(e, dt, node, config) {
                                imprimirEtiquetas();
                            },
                        }]

                    },
                },
            }

        );

        function buscarProducto() {
            tabla_productos.clear().draw();
            $('#product-search-box').val('');
            $('#modalBuscarProducto').modal('show');
        }

        $('#product-search-box').keyup(function() {
            
            let searchString = $(this).val();
            if (searchString != '' && searchString.length >= 3) {
                $.ajax({
                    url: "/odoocpe/barcode/product/search",
                    method: "POST",
                    beforeSend: function() {
                        $("#loading").show();
                    },
                    data: {
                        searchString: searchString,
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
                        let html = '';
                        data.forEach(product => {
                            tabla_productos.row.add(product).draw();
                        });
                    },
                    complete: function() {
                        $("#loading").hide();
                    }
                });
            } else {
                $('#tbl_productos tbody').html('');
            }
        });



        /***** CARGAR PRODUCTO A LA BANDEJA */
      
        $('#tbl_productos tbody').on('click', 'tr', function() {

            // obtenemos los datos de la fila seleccionada
            var data = tabla_productos.row(this).data();
            var boton =
                "<span " +
                "class='btnQuitar px-1 botonodooico' style='cursor: pointer;' title='Quitar de la lista'>" +
                "<i class='fa-solid fa-trash'></i>" +
                "</span> " +
                "</center>";



            // seteamos la variable con Id para usarla al momento de guardar el pago
            idProducto = data["id"];

            var existe = table_bandeja
                .column(1)
                .data()
                .filter(function(value, index) {
                    return value == idProducto ? true : false;
                });
            // buscar el nro de item mayor para agregar el nuevo producto sin usa la funcion max
           
            let nroitem = 1;

            // si la tabla bandeja no esta vacia se busca el nro de item mayor
            if (table_bandeja.rows().count() > 0) {
                nroitem = table_bandeja.column(0).data().reduce(function(a, b) {
                    return Math.max(a, b);
                });
                nroitem = nroitem + 1;
            }

            if (existe.length == 0) {
                
                table_bandeja.row.add({
                    'nroitem': nroitem,
                    'id': data["id"],
                    'name': data["name"],
                    'barcode': data["barcode"],
                    'cantidad': 1,
                    'acciones': boton,


                }).draw();
                nroitem = nroitem + 1;
                recalcularTotalImpresiones();
                //quitar fila de la tabla de productos
                tabla_productos.row(this).remove().draw();

            } else {
                alert("el producto ya esta en la badeja de impresion");
            }
            // si la tabla esta vacia se cierra el modal
            if (tabla_productos.rows().count() == 0) {
                $('#modalBuscarProducto').modal('hide');
            }


        });


        /**** borrar de la bandeja */
        $('#tbl_bandeja tbody').on('click', '.btnQuitar', function() {
            // capturamos los datos de la fila donde hacemos click y los cargamos en array 
            var datafila = table_bandeja.row($(this).parents('tr')).data();

            var idx = table_bandeja.row($(this).parents('tr')).index();
            var ncantidad = parseInt(datafila['cantidad']) - parseInt(datafila['cantidad']);

            table_bandeja.cell(idx, 3).data(ncantidad).draw();
            table_bandeja.row($(this).parents('tr')).remove().draw();

            recalcularTotalImpresiones();

        })

        /** DETECTAR EL CAMBIO DEL IMPUT  */

        $(document).on('change', '.iptCantidad', function() {
      
            event.stopPropagation();
            let nuevaCantidad = Number.parseFloat($(this).val());

            let datafila = table_bandeja.row($(this).parents('tr')).data();
            let idx = table_bandeja.row($(this).parents('tr')).index();

            // evita que detecto el cambio al actualizar la celda con la nueva cantidad
            if (typeof idx === 'undefined') {
                //console.log("NO hay idx");
                return;
            }
          

            table_bandeja.cell(idx, 4).data(nuevaCantidad).draw();
         


            recalcularTotalImpresiones();

        });

        /*** RECALCULAR ETIQUETAS */
        function recalcularTotalImpresiones() {


            let TotalEtiquetas = 0;

            table_bandeja.rows().eq(0).each(function(index) {
                var row = table_bandeja.row(index);
                var dato = row.data();

                let cantidad = dato["cantidad"];


                TotalEtiquetas = parseFloat(TotalEtiquetas) + parseFloat(cantidad);
                TotalEtiquetas = Number.parseFloat(TotalEtiquetas)


            })
            // VACIAMOS EL DATATABLES
            if (TotalEtiquetas == 0) {
                table_bandeja.clear().draw();
                console.log("se limpio la tabla");
            }

            $("#TotalEtiquetas").text(TotalEtiquetas);
            let etiquetas_x_hoja = 126;
            let total_hojas = Math.ceil(TotalEtiquetas / etiquetas_x_hoja);
            let etiquetas_faltantes = etiquetas_x_hoja * total_hojas - TotalEtiquetas
            $("#EtiquetasFaltantes").text(etiquetas_faltantes);
            $("#TotalHojas").text(total_hojas);


        }

        function imprimirEtiquetas() {

            Swal.fire({
                title: 'Imprimir Etiquetas',
                html: 'Desea Imprimir las Etiquetas',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si',
                cancelButtonText: 'Cancelar',
            }).then((result) => {

                if (result.isConfirmed) {
                    // Obtener los datos de productos
                    let productos = table_bandeja.rows().data().toArray();
                   
                   // productos.forEach(producto => delete producto.acciones);

                    // Crear un formulario dinámico para enviar los datos a una nueva ventana
                    let form = document.createElement("form");
                    form.method = "POST";
                    form.action = "{{ route('imprimirEtiquetas') }}";
                    form.target = "_blank"; // Abrir en una nueva pestaña
                    form.style.display = "none";

                    // Añadir el token CSRF
                    let csrfField = document.createElement("input");
                    csrfField.type = "hidden";
                    csrfField.name = "_token";
                    csrfField.value = "{{ csrf_token() }}";
                    form.appendChild(csrfField);

                    // Añadir los datos de productos como input hidden
                    let productosField = document.createElement("input");
                    productosField.type = "hidden";
                    productosField.name = "productos";
                    productosField.value = JSON.stringify(productos);
                    form.appendChild(productosField);

                    // Agregar el formulario al DOM y enviarlo
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form); // Eliminar el formulario del DOM
                }



            });

        }
    });
</script>
