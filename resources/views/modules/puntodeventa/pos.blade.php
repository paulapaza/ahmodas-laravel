<x-pos-layout>
    <div class="container-fluid mx-0 px-0">
        <div class="row mb-3 ">
            <div class="col-md-7 pt-3">
                <div class="row">
                    <div class="col-md-12 mb-1">
                        <div class="row form-group mb-3 ">
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
                                    <i class="fa-solid fa-barcode "></i> Buscar Por codigo de barras
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
                    <div class="col-md-12" id="productos-container" style="display: none;">
                        <!-- TABLA PARA CARGAR productos left -->
                        <table id="table-Productos" class="table table-striped w-100">
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
            <div class="col-md-5 bg-xsecondary-soft mr-0 pt-3" style="height: 100vh; overflow-y: auto;">
                <div class="card shadow">
                    <div class="card-body pt-0 px-1">
                        <div id="carrito"
                            style="min-height: 20vh; max-height: 50vh; overflow-y: auto; scrollbar-x: none;">
                            <table id="table-carrito">
                                <thead>
                                    <tr>
                                        <th>Can</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Precio minimo</th>
                                        <th>Sub total</th>
                                        <th>id</th>
                                        <th>tool</th>

                                    </tr>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">

                            <div class="col-md-12 text-right pr-3 my-2 text-lg font-weight-bold justify-content-end">
                                Total
                                <span id="MonedaServicios"></span> <span id="SimboloMonedaservicios"></span> <span
                                    id="TotalRecibo">0.00</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-block btn-xsuccess" id="btnPagar">Pagar</button>
                    </div><!-- card body-->
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Lista de productos -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body" id="lista-productos" style="height: 50vh; overflow-y: auto;">
                        <!-- poner un buyscador para traer los productos -->

                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="buscador-productos"
                                placeholder="Buscar productos...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="btn-buscar-productos">
                                    Buscar
                                </button>
                            </div>
                        </div>
                        <p class="text-muted">Seleccione un producto para agregar al carrito</p>
                        <!-- Aquí se mostrarán los productos -->

                        <div class="row">
                            <!-- Producto de ejemplo (puedes llenar con un foreach en backend o JS) -->
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-outline-dark btn-block agregar-producto" data-id="1"
                                    data-nombre="Producto A" data-precio="10.00">
                                    Producto A<br><small>S/ 10.00</small>
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-outline-dark btn-block agregar-producto" data-id="2"
                                    data-nombre="Producto B" data-precio="20.00">
                                    Producto B<br><small>S/ 20.00</small>
                                </button>
                            </div>
                            <!-- ... -->
                        </div>
                    </div>
                </div>
                {{-- <div class="card">
                    <div class="card-body col-4">
                        <div id="keypad" class="btn-group d-flex flex-wrap" style="gap: 5px;">
                            @php
                                $keys = ['7', '8', '9', '4', '5', '6', '1', '2', '3', '0', '.', '←'];
                            @endphp
                            @foreach ($keys as $key)
                                <button type="button" class="btn btn-secondary flex-fill keypad-btn text-bold"
                                    data-key="{{ $key }}" style="width: 30%; min-width: 60px; margin: 2px; font-size: 1.5em;">
                                    
                                    {{ $key }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div> --}}
                <div class="row">
                    <div class="col-12 text-right">
                        <div class="card d-inline-block" style="width: 260px;">

                            <div class="card-body">
                                <div id="keypad" class="btn-group d-flex flex-wrap justify-content-end"
                                    style="gap: 5px;">
                                    @php
                                        $keys = ['7', '8', '9', '4', '5', '6', '1', '2', '3', '0', '.', '←'];
                                    @endphp
                                    @foreach ($keys as $key)
                                        <button type="button" class="btn btn-secondary keypad-btn"
                                            data-key="{{ $key }}"
                                            style="width: 30%; min-width: 60px; margin: 2px; font-size: 1.6em; font-weight: bold;">
                                            {{ $key }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Carrito -->
            <div class="col-md-5">
                <div class="card">

                    <div class="card-body" style="height: 30vh; overflow-y: auto;">
                        <table class="table table-sm" id="tabla-carrito">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="p-3">
                        {{-- <div class="form-group">
                            <label>Total a pagar:</label>
                            <h3 id="total-carrito">S/ 0.00</h3>
                        </div> --}}

                        <!-- Inputs de pago con botones como etiquetas -->
                        <div class="form-group row align-items-center">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago active"
                                    data-target="efectivo">Efectivo</button>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" class="form-control text-right tipo-pago"
                                    id="pago_efectivo" name="pago_efectivo" value="0">
                            </div>
                        </div>

                        <div class="form-group row align-items-center">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                    data-target="tarjeta">Tarjeta</button>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" class="form-control text-right tipo-pago"
                                    id="pago_tarjeta" name="pago_tarjeta" value="0">
                            </div>
                        </div>

                        <div class="form-group row align-items-center">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                    data-target="yape">Yape / Plin</button>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" class="form-control text-right tipo-pago"
                                    id="pago_yape" name="pago_yape" value="0">
                            </div>
                        </div>

                        <div class="form-group row align-items-center">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                    data-target="transferencia">Transferencia</button>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" class="form-control text-right tipo-pago"
                                    id="pago_transferencia" name="pago_transferencia" value="0">
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <label>Total pagado:</label>
                            <h4 id="total-pagado">S/ 0.00</h4>
                        </div>





                        <div class="row text-center">
                            <div class="col-4">
                                <button class="btn btn-primary w-100" id="btn-boleta">Boleta</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-primary w-100" id="btn-factura">Factura</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-success w-100" id="btn-guardar">Guardar</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</x-pos-layout>

<script>
    $(document).ready(function() {


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
            columns: [{
                    data: 'cantidad',
                    width: '7%',
                },

                {
                    data: 'nombre',
                    width: '50%',
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
                            .precio_unitario,respuesta[0].precio_minimo);
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
            $("#MonedaServicios").text("S/ ");
            $("#SimboloMonedaservicios").text("S/ ");
            $("#TotalRecibo").val(total.toFixed(2));
            $("#total-pagado").text("S/ " + total.toFixed(2));
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
            calcularTotal();
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
                calcularTotal();
            } else {
                // Si la cantidad es 1, eliminamos el producto del carrito
                row.remove().draw();
                calcularTotal();
            }
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
                agregarProductoAlCarrito(data.id, data.nombre, data.precio_unitario, data.precio_minimo);
                calcularTotal();
            }
        });
        // Evento para manejar el cambio de precio unitario en el carrito
        $(document).on('change', '.iptPrecio-unitario', function() {
            let row = table.row($(this).closest('tr'));
            let data = row.data();
            let nuevoPrecio = parseFloat($(this).val());
            if (!isNaN(nuevoPrecio) && nuevoPrecio > data.precio_minimo) {
                data.precio_unitario = nuevoPrecio;
                data.subtotal = (data.cantidad * nuevoPrecio).toFixed(2);
                data.precio_unitario = nuevoPrecio.toFixed(2); // Formatear el
                row.data(data).draw();
                // formaterar con dos decimales
                calcularTotal();
            } else {
                Swal.fire({
                    icon: 'error',
                    html: 'El precio minimo de este producto es : '+ data.precio_minimo,
                    footer: 'Intenta nuevamente!'  
                });
                $(this).val(data.precio_unitario); // Reestablecer al precio original
            }
        });    


    });
</script>
