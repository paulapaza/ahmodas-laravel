<x-pos-layout>
    <div class="container-fluid mx-0 px-0">
        <div class="row mb-3 ">
            <div class="col-md-7 pt-3">
                <div class="row">
                    <div class="col-md-12 mb-1">
                        <div class="row form-group mb-2 ">
                            <div class="col-6 search-list">
                                @csrf
                                <!-- INPUT INGRESO-->
                                <input type="text" class="form-control" id="search-box" placeholder="codigo de barras"
                                    autocomplete="off">
                                <ul id="datos">

                                </ul>
                            </div>
                            <div class="col-5">
                                <!-- BOTONES BUSCAR Producto -->
                                <button class="btn btn-xsecondary wrap w-100" id="search-button">
                                    <i class="fa-solid fa-barcode "></i> Buscar Por codigo de barras (F2)
                                </button>

                            </div>
                            <div class="col-1">
                                <!-- BOTON toggle mostrar y ocultar productos-container -->
                                <button class="btn btn-xsecondary wrap w-100" id="toggle-productos-container">
                                    <i class="fa-solid fa-plus-square"></i>
                                </button>

                            </div>

                        </div>
                    </div>
                    {{-- <div class="col-md-12" id="productos-container" style="display: none;">
                     --}}<div class="col-md-12" id="productos-container">
                        <!-- TABLA PARA CARGAR productos left -->
                        <table id="table-Productos" class="table table-striped w-100 pt-0 mt-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>id</th>
                                    <th>Barcode</th>
                                    <th>Nombre Productos</th>
                                    <th>Precio</th>
                                    <th>Precio Minimo</th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- Carrito-->
            <div class="col-md-5 bg-xsecondary-soft mr-0 pt-2" style="height: 100vh; overflow-y: auto;">
                <div class="card shadow bg-none">
                    <div class="card-body pt-0 px-1">
                        <div id="carrito" style="height: 39vh; overflow-y: auto; overflow-x: hidden;">
                            <table id="table-carrito">
                                <thead>
                                    <tr>
                                        <th>Can</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Precio minimo</th>
                                        <th>Total</th>
                                        <th>id</th>
                                        <th></th>

                                    </tr>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            
                            <div class="col-md-6 text-left pl-4 my-2">
                                <label for="moneda" class="font-weight-bold">Moneda:</label>
                                <select class="form-control d-inline-block w-auto" id="moneda"
                                    name="moneda" required>
                                    <option value="1" selected>PEN</option>
                                    <option value="2">USD</option>
                                </select>
                            </div>
                            <div class="col-md-6 text-right pr-3 my-2 text-lg font-weight-bold justify-content-end">
                               
                                Total
                                
                                <span id="simbolo_moneda">S/.</span>
                                <span id="TotalRecibo">0.00</span>
                            </div>
                        </div>
                        <div class="row align-items-center mb-0">
                            <div class="col-6">
                                <div class="form-group row pl-3">
                                    <div class="col-8">
                                        <button type="button"
                                            class="btn btn-outline-secondary btn-block btn-modo-pago active"
                                            data-target="efectivo">Efectivo</button>
                                    </div>
                                    <div class="col-4 px-0">
                                        <input type="text" class="form-control text-right tipo-pago"
                                            id="pago_efectivo" name="pago_efectivo" value="0" disabled>
                                    </div>
                                </div>

                                <div class="form-group row pl-3">
                                    <div class="col-8">
                                        <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                            data-target="tarjeta">Tarjeta</button>
                                    </div>
                                    <div class="col-4 px-0">
                                        <input type="text" class="form-control text-right tipo-pago"
                                            id="pago_tarjeta" name="pago_tarjeta" value="0" disabled>


                                    </div>
                                </div>

                                <div class="form-group row pl-3">
                                    <div class="col-8">
                                        <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                            data-target="yape">Yape / Plin</button>
                                    </div>
                                    <div class="col-4 px-0">
                                        <input type="text" class="form-control text-right tipo-pago" id="pago_yape"
                                            name="pago_yape" value="0" disabled>
                                    </div>
                                </div>

                                <div class="form-group row pl-3">
                                    <div class="col-8">
                                        <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                            data-target="transferencia">Transferencia</button>
                                    </div>
                                    <div class="col-4 px-0">
                                        <input type="text" class="form-control text-right tipo-pago"
                                            id="pago_transferencia" name="pago_transferencia" value="0" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card d-inline-block ">


                                    <div class="card-body py-1">
                                        <div id="keypad" class="btn-group d-flex flex-wrap justify-content-end"
                                            style="gap: 4px;">
                                            @php
                                                $keys = ['7', '8', '9', '4', '5', '6', '1', '2', '3', '0', '.', '←'];
                                            @endphp

                                            @foreach ($keys as $key)
                                                <button type="button" class="btn btn-secondary keypad-btn"
                                                    data-key="{{ $key }}"
                                                    style="width: 30%; min-width: 40px; margin: 1px; font-size: 1.2em; font-weight: bold; padding: 5px 8px;">
                                                    {{ $key }}
                                                </button>
                                            @endforeach

                                            {{-- Botón Enter (✔) --}}
                                            <button type="button" class="btn btn-success keypad-enter"
                                                style="width: 80%; min-width: 60px; margin: 2px; font-size: 1.2em; font-weight: bold; padding: 5px 8px;">
                                                ✔
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class="row justify-content-around">
                            <button type="button" class="btn btn-xsuccess col-3 procesar_venta" id="btnBoleta"
                                codigo_tipo_comprobante="03">Boleta</button>
                            <button type="button" class="btn btn-xsuccess col-3 procesar_venta" id="btnFactura"
                                codigo_tipo_comprobante="01">Factura</button>
                            <button type="button" class="btn btn-xsuccess col-3 procesar_venta" id="btnGuardar"
                                codigo_tipo_comprobante="12">Guardar</button>
                        </div>
                    </div><!-- card body-->
                </div>
            </div>
        </div>

    </div>

</x-pos-layout>

<script>
    $(document).ready(function() {
        $('#search-box').focus();
        let totalCarrito = 0;
        let _token = $('input[name="_token"]').val();
        // Inicializamos las variables para tipo de envio por ajax en Store record
        let dataCrud = {
            route: "/inventario/producto",
            subject: 'Producto',
            model: "producto",
            csrf: _token,
        };
        let tableProductos = new Larajax({
            data: dataCrud,
            idTable: "#table-Productos",
            topButton: false,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'codigo_barras'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'precio_unitario'
                },
                {
                    data: 'precio_minimo',
                    visible: false

                },



            ],
            /*  actionsButtons: {
                 edit: true,
                 destroy: true
             },
             alingCenter: [3, 4] */
        })

        let table = new DataTable('#table-carrito', {
            paging: false,
            searching: false,
            ordering: false,
            info: false,
            lengthChange: false,
            language: {
                emptyTable: ' '
            },

            columns: [{
                    data: 'cantidad',
                    width: '7%',
                },

                {
                    data: 'nombre',
                    width: '48%',
                },
                {
                    data: 'precio_unitario',
                    className: 'text-right',
                    render: function(data, type, row) {

                        return `<input type="text" min="1" class="iptPrecio-unitario" 
                                    style="text-align: center; width:65px; border-radius: 5px; border: 1px solid #ced4da;"
                                    value="${data}" />`;



                    }
                },
                {
                    data: 'precio_minimo',
                    visible: false
                },
                {
                    data: 'subtotal',
                    className: 'text-right',


                },
                {
                    data: 'id',
                    visible: false
                },
                {
                    data: 'boton',
                    width: '15%',

                },



            ]
        });


        //buscar producto 
        $("#search-box").keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault(); // Evita el comportamiento por defecto del enter
                buscarProducto();
            }
        });
        $(document).on('click', '#search-button', function(e) {

            e.stopImmediatePropagation();
            buscarProducto();

        })

        function buscarProducto() {
            var stringSearch = $("#search-box").val();

            $("#search-box").focus();

            if (isNaN(stringSearch) == true) {
                Swal.fire({
                    icon: 'error',
                    html: 'El codigo debe ser solo numeros',
                    footer: 'Intenta nuevamente!'
                })
                return
            }

            $.ajax({
                url: "/invetario/producto/buscar",
                type: "POST",
                data: {
                    '_token': _token,
                    "stringSearch": stringSearch,
                },
                dataType: 'json',
                success: function(respuesta) {

                    $("#search-box").focus();
                    $("#search-box").val("");
                    /*
                    {id: 2, codigo_barras: "452978", nombre: "pantalon jean varon wrangler 42", costo_unitario: "50.00",…}

                    */
                    //cargar data al carrito
                    if (respuesta.length > 0) {
                        agregarProductoAlCarrito(respuesta[0].id, respuesta[0].nombre, respuesta[0]
                            .precio_unitario, respuesta[0].precio_minimo);
                        calcularTotal();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: 'No se encontro el producto',
                            footer: 'Intenta nuevamente!'
                        })
                    }



                }
            });

        }
        //calcular total
        function calcularTotal() {
            let total = 0;
            table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                let data = this.data();
                total += parseFloat(data.subtotal);
            });
            $("#TotalRecibo").text(total.toFixed(2));
            totalCarrito = total;
        }
        // agregar producto al carrito
        function agregarProductoAlCarrito(id, nombre, precio, precio_minimo) {
            //revisar si el producto ya existe en el carrito
            let existe = false;
            table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                let data = this.data();
                if (data.id === id) {
                    // Si el producto ya existe, aumentamos la cantidad
                    data.cantidad++;
                    data.subtotal = (data.cantidad * parseFloat(data.precio_unitario)).toFixed(2);
                    this.data(data).draw();
                    existe = true;
                    calcularTotal();
                    distribuirTotalEnEfectivo();
                    return false; // Salimos del loop
                }
            });
            // Si el producto no existe, lo agregamos al carrito
            if (existe) return;


            let data = {
                cantidad: 1,
                nombre: nombre,
                precio_unitario: precio,
                precio_minimo: precio_minimo, // Asumiendo que el precio mínimo es el mismo que el unitario
                subtotal: precio,
                id: id,
                boton: `
                        <button class="btn btn-secondary btn-xs disminuir-cantidad" data-id="${id}"><i class="fa-solid fa-minus"></i></button>
                        <button class="btn btn-secondary btn-xs aumentar-cantidad" data-id="${id}"><i class="fa-solid fa-plus"></i></button>
                        `
            };
            table.row.add(data).draw();
            // cargar el total al input efectivo
            calcularTotal();
            distribuirTotalEnEfectivo();
        }
        // Evento para aumentar la cantidad de un producto en el carrito
        $(document).on('click', '.aumentar-cantidad', function() {
            let id = $(this).data('id');
            let row = table.row($(this).closest('tr'));
            let data = row.data();
            data.cantidad++;
            data.subtotal = (data.cantidad * parseFloat(data.precio_unitario)).toFixed(2);
            row.data(data).draw();
            calcularTotal();
            distribuirTotalEnEfectivo();
        });
        // Evento para disminuir la cantidad de un producto en el carrito
        $(document).on('click', '.disminuir-cantidad', function() {

            let id = $(this).data('id');
            let row = table.row($(this).closest('tr'));
            let data = row.data();
            if (data.cantidad > 1) {
                data.cantidad--;
                data.subtotal = (data.cantidad * parseFloat(data.precio_unitario)).toFixed(2);
                row.data(data).draw();


            } else {
                // Si la cantidad es 1, eliminamos el producto del carrito
                row.remove().draw();
            }
            calcularTotal();
            distribuirTotalEnEfectivo();
        });
        // toggle productos-container
        $(document).on('click', '#toggle-productos-container', function(e) {
            e.stopImmediatePropagation();
            $("#productos-container").toggle();
            if ($("#productos-container").is(":visible")) {
                $(this).html('<i class="fa-solid fa-minus-square"></i>');
            } else {
                $(this).html('<i class="fa-solid fa-plus-square"></i>');
            }
        });
        // Evento para agregar un producto al carrito desde la lista de productos 
        $("#table-Productos tbody").on("click", "tr", function() {
            let data = tableProductos.row(this).data();
            if (data) {
                agregarProductoAlCarrito(data.id, data.nombre, data.precio_unitario, data
                    .precio_minimo);
                calcularTotal();
            }
        });
        // Evento para manejar el cambio de precio unitario en el carrito
        $(document).on('change', '.iptPrecio-unitario', function() {
            let row = table.row($(this).closest('tr'));
            let data = row.data();
            let nuevoPrecio = parseFloat($(this).val());
            if (!isNaN(nuevoPrecio) && nuevoPrecio >= data.precio_minimo) {
                data.precio_unitario = nuevoPrecio;
                data.subtotal = (data.cantidad * nuevoPrecio).toFixed(2);
                data.precio_unitario = nuevoPrecio.toFixed(2); // Formatear el
                row.data(data).draw();

            } else {
                Swal.fire({
                    icon: 'error',
                    html: 'El precio minimo de este producto es : ' + data.precio_minimo,
                    footer: 'Intenta nuevamente!'
                });
                $(this).val(data.precio_unitario); // Reestablecer al precio original
            }
            calcularTotal();
            distribuirTotalEnEfectivo(); // Recalcular efectivo al cambiar el precio
        });
        // alñ presionar enterel iptPrecio-unitario

        // otro eventos
        // Detecta el último input tocado
        $('body').on('focus', 'input', function() {
            inputActivo = this;
        });

        let nuevoValor = '';

        $('.keypad-btn').click(function() {
            if (!inputActivo) return;
            console.log('Valor actualizado:', inputActivo.value);
            if (!inputActivo) return;

            const key = $(this).data('key');

            if (key === '←') {
                nuevoValor = nuevoValor.slice(0, -1);
            } else {
                nuevoValor += key;
            }
            inputActivo.value = nuevoValor;

            //$(inputActivo).trigger('input'); // Para actualizar cálculos si los hay
        });
        $('#table-carrito input').blur(function() {
            if (!inputActivo) return;

            const row = table.row($(this).closest('tr'));
            const data = row.data();
            const valorFinal = parseFloat($(this).val());

            if (valorFinal < data.precio_minimo) {
                Swal.fire({
                    icon: 'error',
                    html: 'El precio mínimo de este producto es: ' + data.precio_minimo,
                    footer: 'Intenta nuevamente!'
                });

                nuevoValor = data.precio_unitario.toString();
                this.value = nuevoValor;
            }

            inputActivo = null;
            nuevoValor = '';
        });
        $('.keypad-enter').click(function() {
            if (!inputActivo) return;

            if ($(inputActivo).closest('#table-carrito').length) {
                const row = table.row($(inputActivo).closest('tr'));
                const data = row.data();
                const valorFinal = parseFloat(nuevoValor);

                if (isNaN(valorFinal)) return;

                if (valorFinal >= data.precio_minimo) {
                    data.precio_unitario = valorFinal.toFixed(2);
                    data.subtotal = (data.cantidad * valorFinal).toFixed(2);
                    row.data(data).draw();
                    inputActivo.value = valorFinal.toFixed(2);
                } else {
                    Swal.fire({
                        icon: 'error',
                        html: 'El precio mínimo de este producto es: ' + data.precio_minimo,
                        footer: 'Intenta nuevamente!'
                    });

                    // Restaurar valor anterior
                    nuevoValor = data.precio_unitario.toString();
                    inputActivo.value = nuevoValor;
                }

                calcularTotal();
                distribuirTotalEnEfectivo();
            }
            //para usarlo en los imputs de pago
            else if ($(inputActivo).closest('.tipo-pago').length) {
                const valorFinal = parseFloat(nuevoValor);
                if (isNaN(valorFinal)) return;

                inputActivo.value = valorFinal.toFixed(2);
                manejarCambioManual(inputActivo.id.replace('pago_', ''));
            }
            // si el valor del input es cero desabilitar el input
            if (parseFloat(inputActivo.value) <= 0) {
                $(inputActivo).prop('disabled', true);
            }
            // Limpiar
            inputActivo = null;
            nuevoValor = '';
        });



        function distribuirTotalEnEfectivo() {
            $('#pago_efectivo').val(totalCarrito.toFixed(2));
            $('#pago_tarjeta, #pago_yape, #pago_transferencia').val(0).prop('disabled', true);
            calcularTotal();
        }



        // Cuando cambia manualmente un input (tarjeta, yape o transferencia)
        function manejarCambioManual(target) {
            const valorManual = parseFloat($(`#pago_${target}`).val()) || 0;
            const otros = ['efectivo', 'tarjeta', 'yape', 'transferencia'].filter(x => x !== target);

            let sumaOtros = 0;
            otros.forEach(m => {
                if (m !== 'efectivo') {
                    sumaOtros += parseFloat($(`#pago_${m}`).val()) || 0;
                }
            });

            const restante = totalCarrito - (valorManual + sumaOtros);
            $('#pago_efectivo').val(Math.max(0, restante).toFixed(2));
            //si el valor es cero desabilitar el input

            //calcularTotal();
        }

        // Cuando se presiona un botón (Yape, Tarjeta, Transferencia)
        function moverDesdeEfectivo(metodoDestino) {

            const montoEfectivo = parseFloat($('#pago_efectivo').val()) || 0;
            const actualDestino = parseFloat($(`#pago_${metodoDestino}`).val()) || 0;

            const nuevoMonto = montoEfectivo + actualDestino;
            $(`#pago_${metodoDestino}`).val(nuevoMonto.toFixed(2));
            $('#pago_efectivo').val(0);
            //calcularTotal();
        }

        // Al hacer clic en método de pago
        $('.btn-modo-pago').click(function() {
            //si efectivo es cero, hno hacer nada

            $('.btn-modo-pago').removeClass('active');
            $(this).addClass('active');
            const metodo = $(this).data('target');
            //si es efectivo ignorar
            if (metodo === 'efectivo') {

                $('#pago_efectivo').val(totalCarrito.toFixed(2));
                $('#pago_tarjeta, #pago_yape, #pago_transferencia').prop('disabled', true).val(0);
                return;
            }
            if ($('#pago_efectivo').val() <= 0) {
                return;
            }
            metodoPagoActual = metodo;
            moverDesdeEfectivo(metodo);

            //poner el foco en el input del metodo de pago
            $(`#pago_${metodo}`).focus();
            // enable input del metodo de pago
            $(`#pago_${metodo}`).prop('disabled', false);
        });

        // Al editar manualmente un input
        $('.tipo-pago').on('input', function() {
            const id = $(this).attr('id').replace('pago_', '');
            manejarCambioManual(id);
        });
        // al precionar enter dentro del input de pago
        $('.tipo-pago').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault(); // Evita el comportamiento por defecto del enter
                const id = $(this).attr('id').replace('pago_', '');
                // poner dos decimales al valor
                $(this).val(parseFloat($(this).val()).toFixed(2));
                manejarCambioManual(id);
                // Deshabilitar el input si es cero
                if (parseFloat($(this).val()) <= 0) {
                    $(this).prop('disabled', true);
                }
            }
        });
        // poner foco en el input de codigo de barras al cargar la pagina al presionar f2
        $(document).on('keydown', function(e) {
            if (e.key === 'F2') {
                e.preventDefault();
                $('#search-box').focus();
            }
        });
        /****************************
         * PROCESAR PAGO
         ****************************/
        $(document).on('click', '.procesar_venta', function() {
            let total = parseFloat($("#TotalRecibo").text());
            if (total <= 0) {
                Swal.fire({
                    icon: 'error',
                    html: 'No hya producto agregados al la venta',
                    footer: 'Agrega productos al carrito!'
                });
                $('.procesar_venta').prop('disabled', false);
                return;
            }
            $('.procesar_venta').prop('disabled', true);

            let codigo_tipo_comprobante = $(this).attr('codigo_tipo_comprobante');
            let cliente = null;
            // abrir modal si el codigo_tipo_comprobante es 01 o 03
            if (codigo_tipo_comprobante != "12") {
                // si es 03 cargar variables para boleta
                if (codigo_tipo_comprobante == "03") {
                    Swal.fire({
                        title: 'Datos cliente para la boleta',
                        html: `<div class="form-contenedor">
                                <input type="text" id="dni_cliente" class="form-input" placeholder="DNI">
                                <input type="text" id="nombre_cliente" class="form-input" placeholder="Nombre">
                                <input type="text" id="direccion_cliente" class="form-input" placeholder="Dirección">
                                </div>`,
                        focusConfirm: false,
                        customClass: {
                            popup: 'form-modal'
                        },
                        preConfirm: () => {
                            const dni = Swal.getPopup().querySelector('#dni_cliente').value;
                            const nombre = Swal.getPopup().querySelector('#nombre_cliente')
                                .value;
                            const direccion = Swal.getPopup().querySelector(
                                '#direccion_cliente').value;


                            // dni 8 digitos y ser solo numeros
                            if (dni && !/^\d{8}$/.test(dni)) {
                                Swal.showValidationMessage(
                                    'El DNI debe tener exactamente 8 dígitos numéricos');
                                return false;
                            }
                            // si tiene dni de tener nombre
                            if (dni && !nombre) {
                                Swal.showValidationMessage(
                                    'Falta Nombre');
                                return false;
                            }

                            return {
                                dni,
                                nombre,
                                direccion
                            };
                        },
                        willClose: () => {
                            // Se ejecuta siempre al cerrar el modal, incluso si no se confirmó
                            $('.procesar_venta').prop('disabled', false);
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Aquí accedes a los datos ingresados
                            const cliente = result.value; // Aquí están los valores ingresados
                            //añadir tipo_documento a cliente
                            cliente.tipo_documento = '1';
                            procesarVenta(cliente); // Llama a tu función con el cliente

                        }

                    });
                }
                if (codigo_tipo_comprobante == "01") {
                    Swal.fire({
                        title: 'Datos cliente para la factura',
                        html: `<div class="form-contenedor"><input type="text" id="ruc_cliente" class="form-input" placeholder="RUC">
                                <input type="text" id="razon_social_cliente" class="form-input" placeholder="Razón Social">
                                <input type="text"  id="direccion_cliente" class="form-input"  placeholder="Dirección">
                                </div>`,
                        focusConfirm: false,
                        customClass: {
                            popup: 'form-modal'
                        },
                        preConfirm: () => {
                            const ruc = Swal.getPopup().querySelector('#ruc_cliente').value;
                            const razonSocial = Swal.getPopup().querySelector(
                                    '#razon_social_cliente')
                                .value;
                            const direccion = Swal.getPopup().querySelector(
                                '#direccion_cliente').value;

                            if (!ruc || !razonSocial || !direccion) {
                                Swal.showValidationMessage(
                                    `Por favor, completa todos los campos`);
                            }
                            // ruc 11 digitos y ser solo numeros
                            if (!/^\d{11}$/.test(ruc)) {
                                Swal.showValidationMessage(
                                    `El RUC debe tener exactamente 11 dígitos numéricos`
                                );
                                return false;
                            }
                            return {
                                ruc,
                                razonSocial,
                                direccion
                            };
                        },
                        willClose: () => {
                            // Se ejecuta siempre al cerrar el modal, incluso si no se confirmó
                            $('.procesar_venta').prop('disabled', false);
                        }

                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Aquí accedes a los datos ingresados
                            const cliente = result.value; // Aquí están los valores ingresados
                            cliente.tipo_documento = '6'; // Añadir tipo_documento a cliente
                            procesarVenta(cliente); // Llama a tu función con el cliente

                        }
                    });
                }
            } else {
                procesarVenta(cliente);
            }

            //desactivar botones de pago

            //añadir icono des procesamiento
            function procesarVenta(cliente) {

                // Validar que al menos un método de pago tenga un monto
                const efectivo = parseFloat($('#pago_efectivo').val()) || 0;
                const tarjeta = parseFloat($('#pago_tarjeta').val()) || 0;
                const yape = parseFloat($('#pago_yape').val()) || 0;
                const transferencia = parseFloat($('#pago_transferencia').val()) || 0;

                if (efectivo + tarjeta + yape + transferencia <= 0) {
                    Swal.fire({
                        icon: 'error',
                        html: 'Debe ingresar al menos un monto en los métodos de pago',
                        footer: 'Intenta nuevamente!'
                    });
                    return;
                }

                if (efectivo + tarjeta + yape + transferencia != totalCarrito) {

                    Swal.fire({
                        icon: 'error',
                        html: 'El total de los métodos de pago debe ser igual al total del carrito',
                        footer: 'Intenta nuevamente!'
                    });
                    $('.procesar_venta').prop('disabled', false);
                    return;
                }
                //ajax 
                let productos = table.rows().data().toArray();
                // Convertir los productos a un formato adecuado para enviar al servidor
                productos = productos.map(producto => {
                    return {
                        id: producto.id,
                        cantidad: producto.cantidad,
                        precio_unitario: parseFloat(producto.precio_unitario),
                        subtotal: parseFloat(producto.subtotal)
                    };
                });
                $.ajax({
                    url: "/punto-de-venta/venta",
                    type: "POST",
                    data: {
                        '_token': _token,
                        'efectivo': efectivo,
                        'tarjeta': tarjeta,
                        'yape': yape,
                        'transferencia': transferencia,
                        'moneda': $('#moneda').val(),
                        'total': totalCarrito,
                        'codigo_tipo_comprobante': codigo_tipo_comprobante,
                        'cliente': cliente,
                        'productos': productos,
                    },
                    dataType: 'json',
                    success: function(respuesta) {
                        if (respuesta.success) {
                            let mensaje = respuesta.message;
                            let footer = '';
                            if (respuesta.pos_order.tipo_comprobante != 12) {
                                // impirmir el mensje y añadir dos botones para abri una url
                                mensaje = `<div class="text-center">
                                    <h5>Comprobante: ${respuesta.pos_order.tipo_comprobante}</h5>
                                    <a href="${respuesta.cpe_response.enlace_del_pdf}" target="_blank" class="btn btn-primary">Imprimir Comprobante</a>
                                    
                                </div>`;
                                footer = `<div class="text-center">
                                   <a href="${respuesta.cpe_response.enlace}"  target="_blank">ver opciones del Comprobante</a>
                                   
                                </div>`;
                            }
                            Swal.fire({
                                icon: 'success',
                                html: mensaje,
                                footer: footer,
                                // sin boton de cerrar
                                showCloseButton: false,

                            });
                            // Limpiar el carrito
                            table.clear().draw();
                            // Limpiar los inputs de pago
                            $('#pago_efectivo, #pago_tarjeta, #pago_yape, #pago_transferencia')
                                .val('0.00').prop('disabled', true);
                            // Volver a activar los botones de pago
                            $('.procesar_venta').prop('disabled', false);
                            // Actualizar el total del recibo
                            $("#TotalRecibo").text('0.00');
                            // Enfocar el input de búsqueda
                            $('#search-box').focus();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                html: respuesta.message,
                                footer: 'Intenta nuevamente!'
                            });
                            $("#TotalRecibo").text('0.00');
                            // Volver a activar los botones de pago
                            $('.procesar_venta').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            html: 'Error al procesar el pago: ' + xhr.responseJSON
                                .message,
                            footer: 'Intenta nuevamente! presione f5 para recargar la pagina    '
                        });
                    }
                });
            }

        });

        // manejar el cambio de moneda
        $('#moneda').change(function() {
            let monedaSeleccionada = $(this).val();
            if (monedaSeleccionada == '1') {
                // Si es PEN, mostrar el símbolo de Sol
                $('#simbolo_moneda').text('S/ ');
            } else if (monedaSeleccionada == '2') {
                // Si es USD, mostrar el símbolo de Dólar
                $('#simbolo_moneda').text('$ ');
            }
        });



    });
</script>
