<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Productos</x-slot>

    <x-table>
        <th>id</th>
        <th>CodBar</th>
        <th>Nombre</th>
        <th>costo</th>
        <th>precio</th>
        <th>categoria</th>
        <th>stock</th>
        <th>estado</th>
        <th>acciones</th>
    </x-table>

    <x-mymodal :size="'modal-xl'">
        @csrf
        <input type="hidden" id="id" name="id">
        <div class="casrd card-tabss bsorder-0 my-0 mx-0" id="carsd-producto">
            <div class="card-header border-0">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" autofocus required>

                </div>

                <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link active" id="custom-tabs-three-home-tab" data-toggle="pill"
                            href="#custom-tabs-three-home" role="tab" aria-controls="custom-tabs-three-home"
                            aria-selected="true">Informacion general</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-three-messages-tab" data-toggle="pill"
                            href="#custom-tabs-three-messages" role="tab" aria-controls="custom-tabs-three-messages"
                            aria-selected="false">Otros Datos</a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                            href="#custom-tabs-three-profile" role="tab" aria-controls="custom-tabs-three-profile"
                            aria-selected="false">Venta</a>
                    </li> --}}
                    <li class="nav-item">
                        <a class="nav-link" id="custom-tabs-three-compra-tab" data-toggle="pill"
                            href="#custom-tabs-three-compra" role="tab" aria-controls="custom-tabs-three-compra"
                            aria-selected="false">Compra</a>
                    </li>

                </ul>
            </div>
            <div class="card-body">

                <div class="tab-content" id="custom-tabs-three-tabContent">
                    <div class="tab-pane fade show active" id="custom-tabs-three-home" role="tabpanel"
                        aria-labelledby="custom-tabs-three-home-tab">

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <!-- select tipo de producto almacenable o servicio -->
                                <div class="mb-3 row">
                                    <label for="tipo" class="form-label col-6">Tipo de Producto</label>
                                    <select class="form-control col-5" id="tipo" name="tipo" clean="false"
                                        required>
                                        <option value="A" selected>Almacenable</option>
                                        <option value="S">Servicio</option>
                                    </select>
                                </div>
                                <!-- select unidad de medida , unidades , litros , kilo  por defecto unidades -->
                                <div class="mb-3 row">
                                    <label for="tipo" class="form-label col-6">Unidad de medida</label>
                                    <select class="form-control col-5" id="unidad_medida_id" name="unidad_medida_id"
                                        clean="false" required>

                                    </select>
                                </div>
                                <!-- select categoria -->
                                <div class="mb-3 row">
                                    <label for="tipo" class="form-label col-6">Categoria</label>
                                    <select class="form-control col-5" id="categoria_id" name="categoria_id"
                                        clean="false" required>

                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">

                                <div class="mb-3 row">
                                    <label for="precio_unitario" class="form-label col-6">Precio de Venta </label>
                                    <input type="number" class="form-control col-5" id="precio_unitario"
                                        name="precio_unitario" autocomplete="off" required
                                        step="0.01">
                                        >
                                    <small class="text-muted text-right w-100 pr-5 mt-2"
                                        id="precio_segun_impuesto"></small>
                                </div>
                                <div class="mb-3 row">
                                    <label for="venta_impuesto_id" class="form-label col-6">Impuesto al cliente</label>
                                    <select class="form-control col-5" id="venta_impuesto_id" name="venta_impuesto_id" required>
                                    </select>
                                </div>
                                
                                <div class="mb-3 row">
                                    <label for="costo_unitario" class="form-label col-6">Precio de Compra </label>
                                    <input type="number" class="form-control col-5" id="costo_unitario" step="0.01"
                                        name="costo_unitario" autocomplete="off" required>
                                </div>
                                <div class="mb-3 row">
                                    <label for="barcode" class="form-label col-6">Código de barras</label>
                                    <input type="text" class="form-control col-5" id="barcode" name="barcode"
                                        autocomplete="off" required>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{--   <div class="tab-pane fade" id="custom-tabs-three-profile" role="tabpanel"
                        aria-labelledby="custom-tabs-three-profile-tab">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3 row">
                                    <label for="username" class="form-label col-6">Tipo Afectacion al IGV (Impuestos al cliente)</label>
                                    <select class="form-control col-5" id="tipoafectacionigv"
                                        name="tipo_afectacion_igv_id" required>

                                    </select>
                                </div>
                              
                            </div>
                        </div>
                    </div> --}}
                    <div class="tab-pane fade" id="custom-tabs-three-messages" role="tabpanel"
                        aria-labelledby="custom-tabs-three-messages-tab">
                        <div class="row">

                            <div class="col-12 mb-2">
                                <label for="marca" class="form-label">Descripcion del producto</label>
                                <textarea class="form-control col-12" id="descripcion" name="descripcion"></textarea>
                            </div>
                            <div class="col-12 col-md-6 mb-3">

                                <!-- input upload imagen -->
                                <label for="marca" class="form-label">Imagen</label>

                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="img" id="imageFile"
                                        lang="es" accept=".jpg">
                                    <label class="custom-file-label" for="exampleInputFile">Seleccione la
                                        imagen</label>
                                </div>

                            </div>
                            <div class="col-12 col-md-6 mb-3 text-center">


                                {{--  @if ($empresa->logo)
                                        <img src="{{ asset('/img/' . $empresa->logo) }}" alt="..."
                                            class="img-thumbnail" width="150" height="150">
                                    @else --}}
                                <div id="preimg">

                                    <img src="{{ asset('/img/150.png') }}" alt="..." class="img-thumbnail">
                                </div>
                                {{-- @endif --}}

                            </div>


                            <div class="col-6">
                                <label for="marca" class="form-label">Marca o Fabricante</label>
                                <select class="form-control" id="marca_id" name="marca_id" required>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="status" class="form-label">Estado</label>
                                <select class="form-control" id="estado" name="estado" required>
                                    <option value="1" selected>Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="username" class="form-label col-6">Impuesto a la bolsa
                                    Plastica</label>
                                <select class="form-control col-5" id="impuesto_bolsa" name="impuesto_bolsa"
                                    required>
                                    <option value="1">Si</option>
                                    <option value="0" selected>No</option>


                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="custom-tabs-three-compra" role="tabpanel"
                        aria-labelledby="custom-tabs-three-compra-tab">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3 row">
                                    <label for="compra_impuesto_id" class="form-label col-6">Impuesto de compra</label>
                                    <select class="form-control col-5" id="compra_impuesto_id"
                                        name="compra_tipo_afectacion_igv_codigo" required>
                                    </select>
                                </div>

                                <div class="mb-3 row">
                                    <div class="col-12 col-md-4">
                                        <label for="stock_minimo" class="form-label">Stock Minimo</label>
                                        <input type="number" class="form-control" id="stock_minimo"
                                            name="stock_minimo">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="stock_maximo" class="form-label">Stock Maximo</label>
                                        <input type="number" class="form-control" id="stock_maximo"
                                            name="stock_maximo">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="stock_alerta" class="form-label">Stock Alerta</label>
                                        <input type="number" class="form-control" id="stock_alerta"
                                            name="stock_alerta">
                                    </div>
                                </div>
                                {{--   <div class="mb-3 row">
                                    <label for="username" class="form-label col-6">Impuesto a la bolsa
                                        Plastica</label>
                                    <select class="form-control col-5" id="impuesto_bolsa" name="impuesto_bolsa"
                                        required>
                                        <option value="1">Si</option>
                                        <option value="0" selected>No</option>


                                    </select>
                                </div> --}}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </x-mymodal>

</x-admin-layout>
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    $(document).ready(function() {
        bsCustomFileInput.init();

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
                    data: 'barcode'
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
                    data: 'categoria_id'
                },
                {
                    data: 'stock'
                },
                {
                    data: 'estado'
                },
                {
                    data: 'acciones'
                },
            ],
        })
        // edit record

        $('#table').on('click', '.btn-edit', function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            //limpiar el input file


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

        //subir foto
        $("#imageFile").change(function() {
            filePreview(this);
        });

        function filePreview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    //$('#uploadForm + img').remove();
                    $('#preimg').html("");
                    $('#preimg').html('<img src="' + e.target.result + '" width="150" height="150"/>');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

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
        //cargar select de unidades de medida
        $.ajax({
            url: '/inventario/unidad-de-medida',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                data.forEach(element => {
                    //por defecto seleccionamos unidades id =1
                    if (element.id == 1) {
                        html +=
                            `<option value="${element.id}" selected>${element.nombre}</option>`;
                    } else {
                        html += `<option value="${element.id}">${element.nombre}</option>`;
                    }
                });
                $('#unidad_medida_id').html(html);
            }
        });
        //cargar select de tipo de afectacion al igv
        $.ajax({
            url: '/facturacion/tipo-afectacion-igv',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                data.forEach(element => {
                    if (element.estado == 1) {
                        const isSelected = element.codigo == 10 ? 'selected' : '';
                        html +=
                            `<option value="${element.codigo}" ${isSelected}>${element.codigo} | ${element.descripcion} | ${element.nombre_tributo}</option>`;
                    }
                });
                $('#tipoafectacionigv').html(html);
                $('#compra_tipoafectacionigv').html(html);
            }
        });
        //cargar select de impuestos
        $.ajax({
            url: '/facturacion/impuesto',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                data.forEach(element => {
                    if (element.estado == 1) {
                        html +=
                            `<option value="${element.id}" porcentaje="${element.porcentaje}"" incluye_en_precio="${element.incluido_en_precio}" >${element.nombre}</option>`;
                    }
                });
                $('#venta_impuesto_id').html(html);
                $('#compra_impuesto_id').html(html);
                var impuestosdata = data
            }
        });
        //mostrar  precio de venta sengun el impuesto elegido ()
        $('#venta_impuesto_id').change(function() {
            mostrarTextoImpuesto();
        });
        //mostrar Texto cuando campie el precio unitario
        $('#precio_unitario').keyup(function() {
            mostrarTextoImpuesto();
        });

        mostrarTextoImpuesto();

        function mostrarTextoImpuesto() {

            let impuesto = $('#venta_impuesto_id').find(':selected').attr('porcentaje');
            let incluye_en_precio = $('#venta_impuesto_id').find(':selected').attr('incluye_en_precio');
            let precio = $('#precio_unitario').val();

            //si el impuesto no esta definido no hacemos nada

            if (impuesto == undefined) {
                return;
            }

            if (precio == '') {
                return;
            }
            let texto = '';
            if (incluye_en_precio == 1) {
                // para 10 me tiene que dar 11.80
                precio_venta = parseFloat(precio) / (1 + parseFloat(impuesto) / 100);
                texto = `${precio_venta.toFixed(2)} - Impuesto no incluidos `;
                $('#incluye_igv').val(1);

            } else {
                // para 10 me tiene que dar  8.47
                precio_venta = parseFloat(precio) * (1 + parseFloat(impuesto) / 100);
                texto = `${precio_venta.toFixed(2)} - Impuesto incluidos `;
                $('#incluye_igv').val(0);
            }

            $('#precio_segun_impuesto').html(`${texto}`);
        }
    });
</script>
