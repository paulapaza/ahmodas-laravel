<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Productos</x-slot>

    <x-table>
        <th>id</th>
        <th>Barcode</th>
        <th>Nombre</th>
        <th>costo</th>
        <th>precio</th>
        <th>precio minimo</th>
        <th>categoria</th>
        <th>estado</th>
        <th>acciones</th>
    </x-table>

    <x-mymodal>
        @csrf
        <input type="hidden" id="id" name="id">
        <div class="col-12 col-md-12">
            <div class="mb-3 row">
                <label for="nombre" class="form-label col-6">Nombre del Producto</label>
                <input type="text" class="form-control col-12" id="nombre" name="nombre" autocomplete="off"
                    required>
            </div>
            <div class="mb-3 row">
                <label for="costo_unitario" class="form-label col-6">Precio de Compra </label>
                <input type="number" class="form-control col-5" id="costo_unitario" step="0.01"
                    name="costo_unitario" autocomplete="off" required>
            </div>
            <div class="mb-3 row">
                <label for="precio_unitario" class="form-label col-6">Precio de Venta </label>
                <input type="number" class="form-control col-5" id="precio_unitario" name="precio_unitario"
                    autocomplete="off" required step="0.01">

                <small class="text-muted text-right w-100 pr-5 mt-2" id="precio_segun_impuesto"></small>
            </div>
            <div class="mb-3 row">
                <label for="precio_minimo" class="form-label col-6">Precio de Venta min</label>
                <input type="number" class="form-control col-5" id="precio_minimo" name="precio_minimo"
                    autocomplete="off" required step="0.01">

                <small class="text-muted text-right w-100 pr-5 mt-2" id="precio_segun_impuesto"></small>
            </div>


            <div class="mb-3 row">
                <label for="codigo_barras" class="form-label col-6">Código de barras</label>
                <input type="text" class="form-control col-5" id="codigo_barras" name="codigo_barras" autocomplete="off"
                    required>
            </div>
        
            <div class="mb-3 row">
                <label for="marca_id" class="form-label col-6">Marca</label>
                <select class="form-control col-5" id="marca_id" name="marca_id" clean="false" required>

                </select>
            </div>
            <div class="mb-3 row">
                <label for="categoria_id" class="form-label col-6">Categoria</label>
                <select class="form-control col-5" id="categoria_id" name="categoria_id" clean="false" required>

                </select>
            </div>
            <h5>Stock por tienda</h5>
            <div id="stocks_por_tienda"></div>
        </div>


    </x-mymodal>

</x-admin-layout>
<script>
    $(document).ready(function() {


        let csrf = $('input[name="_token"]').val();
        // Inicializamos las variables para tipo de envio por ajax en Store record
        let dataCrud = {
            route: "/inventario/producto",
            subject: 'producto',
            model: "Producto",
            csrf: csrf,
        };
        let table = new Larajax({
            data: dataCrud,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'codigo_barras',
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'costo_unitario'
                },
                {
                    data: 'precio_unitario'
                },
                {
                    data: 'precio_minimo'
                },
                {
                    data: 'categoria_id'
                },
                {
                    data: 'estado',
                    render: function(data, type, row) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' : '<span class="badge bg-xsecondary text-white">Inactivo</span>'
                    }
                },
                
            ],
            actionsButtons: {
                edit: true,
                destroy:true
            },
            alingCenter: [7]
            
        })
        cargarStocks(); // sin parámetro
        // edit record

        $('#table').on('click', '.btn-edit', function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            //limpiar el input file
            cargarStocks(rowData.id); // Pasamos el id del producto para cargar los stocks

            edit_record(rowData, table, $(this));

        });

        // store record
        $(document).on('click', '.btn-store', function() {

            no_send_form('#form');

            let form = document.getElementById('form');

            if (!form.checkValidity()) {
                return;
            }
            let formData = new FormData(form);
            // console.log(formData);

            store_record(dataCrud, formData, table);

        });

        // destroy record
        $('#table').on("click", ".btn-destroy", function() {
            console.log('destroy')

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

            destroy_record(dataCrud, table, rowData)
        });

        //cargar select de categorias
        $.ajax({
            url: '/inventario/categoria',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                data.forEach(element => {
                    if (element.id == 1) {
                        html +=
                            `<option value="${element.id}" selected>${element.nombre}</option>`;
                    } else {
                        html += `<option value="${element.id}">${element.nombre}</option>`;
                    }
                });
                $('#categoria_id').html(html);
            }
        });
        //cargar select de marcas
        $.ajax({
            url: '/inventario/marca',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                data.forEach(element => {
                    if (element.id == 1) {
                        html +=
                            `<option value="${element.id}" selected>${element.nombre}</option>`;
                    } else {
                        html += `<option value="${element.id}">${element.nombre}</option>`;
                    }
                });
                $('#marca_id').html(html);
            }
        });
        //stock por tiendas
        // Llama esto al cargar la vista o al abrir el modal de crear/editar
        function cargarStocks(productoId = null) {
            $.ajax({
                url: '/inventario/stock',
                data: {
                    producto_id: productoId
                },
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    data.forEach(tienda => {
                        html += `
                    <div class="form-group mb-2">
                        <label for="stock_tienda_${tienda.id}">${tienda.nombre}</label>
                        <input type="number"
                            name="stocks[${tienda.id}]"
                            id="stock_tienda_${tienda.id}"
                            class="form-control"
                            min="0"
                            value="${tienda.stock}">
                    </div>`;
                    });
                    $('#stocks_por_tienda').html(html);
                }
            });
        }





    });
</script>
