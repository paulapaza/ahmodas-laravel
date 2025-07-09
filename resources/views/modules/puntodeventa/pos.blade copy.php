<x-pos-layout>
    <div class="container-fluid mx-0 px-0">
        <div class="row mb-3 ">
            <div class="col-md-7 pt-2">
                <div class="row">
                    <div class="col-md-12 mb-1">
                        <div class="row form-group mb-3 ">
                            <div class="col-6 search-list">
                                @csrf
                                <!-- INPUT INGRESO-->
                                <input type="text" class="form-control" id="search-box"
                                    placeholder="nombre de producto o codigo de barras"
                                    autocomplete="off">
                                <ul id="datos">

                                </ul>
                            </div>
                            <div class="col-6">
                                <!-- BOTONES BUSCAR Producto -->
                                <button class="btn btn-primary wrap w-100" id="search-button">
                                    <i class="fa-solid fa-circle-check"></i> Buscar Producto
                                </button>

                            </div>

                        </div>
                    </div>
                    <div class="col-md-12">
                        <!-- TABLA PARA CARGAR productos left -->
                        <table id="table-Productos" class="table table-striped ">
                            <thead class="bg-primary">
                                <tr>
                                    <th>id</th>
                                    <th>Barcode</th>
                                    <th>Nombre Productos</th>
                                    <th>Precio</th>

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
                <div class="card shadow carrito ">
                    <div class="card-body">
                        <div id="carrito" style="height: 30vh; overflow-y: auto;">
                            <table class="table" id="table-carrito">
                                <thead>
                                    <tr>
                                        <th>Cant.</th>
                                        <th>Nombre</th>
                                        <th>Precio uni</th>
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
                        <button type="button" class="btn btn-block btn-danger" id="btnPagar">Pagar</button>
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
                                <button type="button"
                                    class="btn btn-outline-secondary btn-block btn-modo-pago active"
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
   /*  let carrito = [];
    let totalCarrito = 0;
    let metodoPagoActual = 'efectivo';

    function calcularTotalCarrito() {
        totalCarrito = carrito.reduce((acc, item) => acc + item.precio, 0);
        $('#total-carrito').text('S/ ' + totalCarrito.toFixed(2));
    }

    function actualizarCarrito() {
        let html = '';
        carrito.forEach(item => {
            html += `
            <tr>
                <td>${item.nombre}</td>
                <td class="text-right">S/ ${item.precio.toFixed(2)}</td>
            </tr>
        `;
        });
        $('#tabla-carrito tbody').html(html);
        calcularTotalCarrito();
        distribuirTotalEnEfectivo(); // por defecto todo va a efectivo
    }

    function distribuirTotalEnEfectivo() {
        $('#pago_efectivo').val(totalCarrito.toFixed(2));
        $('#pago_tarjeta, #pago_yape, #pago_transferencia').val(0);
        calcularTotalPagado();
    }

    function calcularTotalPagado() {
        let total = 0;
        $('.tipo-pago').each(function() {
            let val = parseFloat($(this).val()) || 0;
            total += val;
        });
        $('#total-pagado').text('S/ ' + total.toFixed(2));
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
        calcularTotalPagado();
    }

    // Cuando se presiona un botón (Yape, Tarjeta, Transferencia)
    function moverDesdeEfectivo(metodoDestino) {
        const montoEfectivo = parseFloat($('#pago_efectivo').val()) || 0;
        const actualDestino = parseFloat($(`#pago_${metodoDestino}`).val()) || 0;

        const nuevoMonto = montoEfectivo + actualDestino;
        $(`#pago_${metodoDestino}`).val(nuevoMonto.toFixed(2));
        $('#pago_efectivo').val(0);
        calcularTotalPagado();
    }
     */
    $(document).ready(function() {
        let inputActivo = null;

        // Detecta el último input tocado
        $('body').on('focus', 'input', function() {
            inputActivo = this;
        });

        // Keypad funcional
        $('.keypad-btn').click(function() {
            if (!inputActivo) return;

            const key = $(this).data('key');

            if (key === '←') {
                inputActivo.value = inputActivo.value.slice(0, -1);
            } else {
                inputActivo.value += key;
            }

            $(inputActivo).trigger('input'); // dispara evento input para recalcular si hace falta
        });

        // Al hacer clic en producto
        $('.agregar-producto').click(function() {
            const producto = {
                id: $(this).data('id'),
                nombre: $(this).data('nombre'),
                precio: parseFloat($(this).data('precio'))
            };
            carrito.push(producto);
            actualizarCarrito();
        });

        // Al hacer clic en método de pago
        $('.btn-modo-pago').click(function() {
            $('.btn-modo-pago').removeClass('active');
            $(this).addClass('active');

            const metodo = $(this).data('target');
            metodoPagoActual = metodo;
            moverDesdeEfectivo(metodo);
        });

        // Al editar manualmente un input
        $('.tipo-pago').on('input', function() {
            const id = $(this).attr('id').replace('pago_', '');
            manejarCambioManual(id);
        });

        // Botón cobrar
        $('#btn-cobrar').click(function() {
            const total = parseFloat($('#total-carrito').text().replace('S/ ', ''));
            const pagado = parseFloat($('#total-pagado').text().replace('S/ ', ''));

            if (carrito.length === 0) {
                return alert('El carrito está vacío.');
            }

            if (Math.abs(total - pagado) > 0.01) {
                return alert('El total pagado no coincide con el total del carrito.');
            }

            // Aquí puedes enviar datos con AJAX si deseas
            alert('Venta registrada correctamente.');
        });

        /**************************
         * MIS funciones
         * ***************************/
      

    });
</script>
