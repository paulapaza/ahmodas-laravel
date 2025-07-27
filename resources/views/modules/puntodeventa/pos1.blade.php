<x-pos-layout>
    <div class="container-fluid mx-0 px-0">
        <div class="row mb-3 ">
            <div class="col-md-7 pt-3">
                <div class="row">
                    <div class="col-md-12 mb-1">
                        <div class="row form-group mb-2 ">
                            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                                    class="fas fa-bars"></i></a>
                            <div class="col-5 search-list">
                                @csrf
                                <!-- INPUT INGRESO-->
                                <input type="text" class="form-control" id="search-box"
                                    placeholder="codigo de barras (F2)" autocomplete="off">
                                <ul id="datos">

                                </ul>
                            </div>
                            <div class="col-5">
                                <!-- BOTONES BUSCAR Producto -->
                                <button class="btn btn-xsecondary wrap w-100" id="search-button">
                                    <i class="fa-solid fa-barcode "></i> Buscar Codigo de barras
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
            <div class="col-md-5  mr-0 pt-2">
                <div class="card shadow bg-none" >
                    <div class="card-body pt-0 px-1">
                        <div id="carrito" style="height: 34vh; overflow-y: auto; overflow-x: hidden;">
                            <table id="table-carrito" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>Cant</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Precio Min</th>
                                        <th>Total</th>
                                        <th>ID</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            @can('ver-moneda')
                                <div class="col-md-4 text-left pl-4 my-2">
                                    <label for="moneda" class="font-weight-bold">Moneda:</label>
                                    <select class="form-control d-inline-block w-auto" id="moneda" name="moneda"
                                        required>
                                        <option value="1" selected>PEN</option>
                                        <option value="2">USD</option>
                                    </select>
                                </div>
                            @endcan
                            @can('ver-tipo-de-venta')
                                <div class="col-md-4 text-right pr-4 my-2">
                                    <label for="tipo_venta" class="font-weight-bold">Venta:</label>
                                    <select class="form-control d-inline-block w-auto" id="tipo_venta" name="tipo_venta"
                                        required>
                                        <option value="local" selected>Local</option>
                                        <option value="exportacion">Exportación</option>
                                    </select>
                                </div>
                            @endcan
                            <div class="col-md-4 text-right pr-3 my-2 text-lg font-weight-bold justify-content-end">

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
                                            id="pago_transferencia" name="pago_transferencia" value="0"
                                            disabled>
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

{{-- Scripts del sistema POS en orden de dependencia --}}
<script src="{{ asset('js/pos/pos-config.js') }}"></script>
<script src="{{ asset('js/pos/pos-utils.js') }}"></script>
<script src="{{ asset('js/pos/cart-manager.js') }}"></script>
<script src="{{ asset('js/pos/payment-manager.js') }}"></script>
<script src="{{ asset('js/pos/product-search.js') }}"></script>
<script src="{{ asset('js/pos/customer-service.js') }}"></script>
<script src="{{ asset('js/pos/sales-processor.js') }}"></script>
<script src="{{ asset('js/pos/pos-main.js') }}"></script>