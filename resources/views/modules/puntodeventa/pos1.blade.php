<x-pos-layout>
    <div class="container-fluid mx-0 px-0">
        <div class="row mb-3 ">
            <div class="col-12 col-lg-7 pt-3">
                <div class="row">
                    <div class="col-md-12 mb-1">
                        <div class="row form-group mb-2 align-items-center">
                            <!-- Menú hamburguesa -->
                            <div class="col-2 col-sm-1 col-md-1">
                                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                                    <i class="fas fa-bars"></i>
                                </a>
                            </div>

                            <!-- Input buscador -->
                            <div class="col-6 col-sm-4 col-md-5 search-list">
                                @csrf
                                <input type="text" class="form-control" id="search-box"
                                    placeholder="Código de barras (F2)" autocomplete="off" inputmode="none">
                                <ul id="datos"></ul>
                            </div>

                            <!-- Botón buscar -->
                            <div class="col-2 col-sm-3 col-md-3">
                                <button class="btn btn-xsecondary wrap w-100" id="search-button">
                                    <i class="fa-solid fa-barcode"></i>
                                    <span class="d-none d-sm-inline"> Buscar</span>
                                </button>
                            </div>

                            <!-- Botón devoluciones -->
                            <div class="col-2 col-sm-2 col-md-2">
                                <button class="btn btn-warning wrap w-100" id="btn-modal-devoluciones" title="Devoluciones/Cambios" data-toggle="modal" data-target="#modalDevoluciones">
                                    <i class="fa-solid fa-exchange-alt"></i>
                                    <span class="d-none d-sm-inline"> Cambios</span>
                                </button>
                            </div>

                            <!-- Botón toggle -->
                            <div class="col-2 col-sm-3 col-md-1">
                                <button class="btn btn-xsecondary wrap w-100" id="toggle-productos-container">
                                    <i class="fa-solid fa-plus-square"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <hr>
                            <h3 class="text-center mb-0">{{ $tiendaDelUsuario }}</h3>
                            <h5 class="text-muted text-center">{{ Auth::user()->name }}</h5>
                            <hr>
                        </div>
                    </div>
                    <div class="col-md-12" id="productos-container" style="display: none;">
                        <!-- TABLA PARA CARGAR productos left -->
                        <table id="table-Productos" class="table table-striped w-100 pt-0 mt-0">
                            <thead class="bg-primary">
                                <tr>
                                    <th>id</th>
                                    {{-- <th>Barcode</th> --}}
                                    <th>Productos</th>
                                    <th>Alias</th>
                                    <th>Stock</th>
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
            <div class="col-12 col-lg-5  mr-0 pt-2">
                <div class="card shadow bg-none">
                    <div class="card-body pt-0 px-1">
                        <div id="carrito" style="height: 34vh; overflow-y: auto; overflow-x: hidden;">
                            <table id="table-carrito" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>Cant</th>
                                        <th>Alias</th>
                                        <th>-</th>
                                        <th>Precio</th>
                                        <th>+</th>
                                        <th>Precio Min</th>
                                        <th>SubTotal</th>
                                        <th>ID</th>
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
                            <div class="col-md-4 my-2 d-flex justify-content-end ml-auto pr-5 font-weight-bold text-lg">
                                Total: <span id="simbolo_moneda" class="mx-2">S/.</span>
                                <span id="TotalRecibo">0.00</span>
                            </div>
                        </div>
                        <div class="row align-items-center mb-0">
                            <div class="col-12 col-md-6">
                                <div class="form-group row pl-3">
                                    <div class="col-6 col-md-8">
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
                                    <div class="col-6 col-md-8">
                                        <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                            data-target="tarjeta">Tarjeta</button>
                                    </div>
                                    <div class="col-4 px-0">
                                        <input type="text" class="form-control text-right tipo-pago"
                                            id="pago_tarjeta" name="pago_tarjeta" value="0" disabled>


                                    </div>
                                </div>

                                <div class="form-group row pl-3">
                                    <div class="col-6 col-md-8">
                                        <button type="button" class="btn btn-outline-secondary btn-block btn-modo-pago"
                                            data-target="yape">Yape / Plin</button>
                                    </div>
                                    <div class="col-4 px-0">
                                        <input type="text" class="form-control text-right tipo-pago" id="pago_yape"
                                            name="pago_yape" value="0" disabled>
                                    </div>
                                </div>

                                <div class="form-group row pl-3">
                                    <div class="col-6 col-md-8">
                                        <button type="button"
                                            class="btn btn-outline-secondary btn-block btn-modo-pago"
                                            data-target="transferencia">Transferencia</button>
                                    </div>
                                    <div class="col-4 px-0">
                                        <input type="text" class="form-control text-right tipo-pago"
                                            id="pago_transferencia" name="pago_transferencia" value="0"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
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

<!-- Modal Devoluciones -->
<div class="modal fade" id="modalDevoluciones" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fa-solid fa-exchange-alt"></i> Devoluciones y Cambios | <span class="badge badge-light text-dark">{{ $tiendaDelUsuario }}</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light text-dark">
                <div class="row">
                    <!-- Productos Devueltos -->
                    <div class="col-md-6 border-right">
                        <h4 class="text-danger">Productos a Devolver (Entran)</h4>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" id="search-devueltos" placeholder="Escanear producto devuelto (F3)" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="btn-search-devueltos"><i class="fa-solid fa-search"></i></button>
                            </div>
                        </div>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-striped" id="table-devueltos">
                                <thead class="thead-dark">
                                    <tr><th>Cant</th><th>Nombre</th><th>Precio</th><th>Total</th><th></th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <h5 class="text-right text-danger mt-2">Total Devuelto: S/ <span id="total-devueltos">0.00</span></h5>
                    </div>
                    <!-- Productos Nuevos -->
                    <div class="col-md-6">
                        <h4 class="text-success">Productos Nuevos (Salen)</h4>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" id="search-nuevos" placeholder="Escanear producto nuevo (F4)" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="btn-search-nuevos"><i class="fa-solid fa-search"></i></button>
                            </div>
                        </div>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-striped" id="table-nuevos">
                                <thead class="thead-dark">
                                    <tr><th>Cant</th><th>Nombre</th><th>Precio</th><th>Total</th><th></th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <h5 class="text-right text-success mt-2">Total Nuevo: S/ <span id="total-nuevos">0.00</span></h5>
                    </div>
                </div>
                <hr>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 id="texto-diferencia" class="font-weight-bold">Diferencia: S/ 0.00</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <label>Método de Pago:</label>
                        <select id="metodo_pago_devolucion" class="form-control d-inline w-auto">
                            <option value="Efectivo" selected>Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Yape">Yape / Plin</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                        <br>
                        <textarea id="motivo_devolucion" class="form-control mt-2" placeholder="Motivo del cambio/devolución (opcional)" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-procesar-devolucion">Procesar Transacción</button>
            </div>
        </div>
    </div>
</div>

</x-pos-layout>

<script>
    window.userIdPos = {{ Auth::user()->id ?? 1 }};
    window.tiendaIdPos = {{ $idTiendaUsuario ?? 1 }};
    window.AuthUser = @json(Auth::user());
    window.tiendas = @json( $tiendas);
    window.idTiendaUsuario = @json( $idTiendaUsuario);
</script>

{{-- Scripts del sistema POS en orden de dependencia --}}
<script src="{{ asset('js/pos/pos-config.js') }}"></script>
<script src="{{ asset('js/pos/pos-utils.js') }}"></script>
<script src="{{ asset('js/pos/cart-manager.js') }}"></script>
<script src="{{ asset('js/pos/payment-manager.js') }}"></script>
{{-- <script src="{{ asset('js/pos/product-search.js') }}"></script> --}}
<script src="{{ asset('js/pos/product-search.js') }}?v={{ filemtime(public_path('js/pos/product-search.js')) }}"></script>
<script src="{{ asset('js/pos/customer-service.js') }}"></script>
<script src="{{ asset('js/pos/sales-processor.js') }}?v={{ filemtime(public_path('js/pos/sales-processor.js')) }}"></script>
<script src="{{ asset('js/pos/pos-main.js') }}"></script>
<script src="{{ asset('js/pos/devoluciones.js') }}?v={{ filemtime(public_path('js/pos/devoluciones.js')) }}"></script>
