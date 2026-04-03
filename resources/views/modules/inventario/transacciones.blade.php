<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Gestión de Stock (Transacciones)</x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/vue-select@3.20.2/dist/vue-select.css">
        <style>
            .vs--searchable .vs__dropdown-toggle {
                height: 38px;
                border-radius: 0.25rem;
                border: 1px solid #ced4da;
            }

            .v-select-sm .vs__dropdown-toggle {
                height: 31px;
                font-size: 0.875rem;
            }

            .bg-xprimary {
                background-color: #4e73df;
            }

            .shadow-xs {
                box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
            }

            .uppercase {
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .store-btn {
                border: 1px solid #dee2e6;
                transition: all 0.2s;
                cursor: pointer;
                border-radius: 8px;
                background-color: #f8f9fc;
            }

            .store-btn:hover {
                border-color: #4e73df;
                background-color: #eaecf4;
                transform: translateY(-1px);
            }

            .store-btn.active {
                border-color: #4e73df;
                background-color: #4e73df;
                color: white;
                box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2);
            }

            .store-btn.disabled {
                opacity: 0.6;
                cursor: not-allowed;
                pointer-events: none;
            }

            .store-info-label {
                font-size: 0.75rem;
                display: block;
                color: inherit;
            }

            .store-name-label {
                font-size: 0.85rem;
                font-weight: 700;
                display: block;
                color: inherit;
            }
        </style>
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/vue-select@3.20.2/dist/vue-select.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    @endpush

    <div id="transacciones-app" class="container-fluid py-3">
        <!-- Vista Principal (Formulario) -->
        <div class="row" v-if="!ultimoTrasladoId">
            <div :class="form.tipo === 'transferencia' ? 'col-12' : 'col-md-9 col-lg-7 col-xl-6 mx-auto'">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-xprimary text-white py-2">
                        <h5 class="card-title mb-0 small uppercase"><i class="fas fa-exchange-alt mr-2"></i> Nueva
                            Operación de Inventario</h5>
                    </div>
                    <div class="card-body p-3">
                        <b-form @submit.prevent="submitForm">
                            <div class="row">
                                <!-- Columna Izquierda: Configuración y Selección de Productos -->
                                <div :class="form.tipo === 'transferencia' ? 'col-lg-4 border-right' : 'col-12'">

                                    <!-- Selección de Tipo de Movimiento -->
                                    <b-form-group label="Tipo de Movimiento:" label-class="small font-weight-bold mb-1">
                                        <b-form-radio-group v-model="form.tipo" :options="opcionesMovimientoFiltradas"
                                            buttons button-variant="outline-primary" class="w-100 btn-group-sm mb-2"
                                            required></b-form-radio-group>
                                    </b-form-group>

                                    <!-- Panel de Selección de Producto -->
                                    <div class="bg-light p-3 rounded border shadow-xs">
                                        <h6
                                            class="text-primary border-bottom pb-2 mb-3 small uppercase font-weight-bold">
                                            <i class="fas"
                                                :class="form.tipo === 'transferencia' ? 'fa-cart-plus' : 'fa-box'"></i>
                                            @{{ form.tipo === 'transferencia' ? 'Agregar al Listado' : 'Selección de Producto' }}
                                        </h6>

                                        <!-- Buscador de Producto -->
                                        <b-form-group label="Producto:" label-class="small font-weight-bold mb-1">
                                            <v-select v-model="productoSeleccionado" :options="productos" label="nombre"
                                                :filterable="false" @search="onSearchProduct"
                                                placeholder="Nombre o código..." class="v-select-sm"
                                                ref="productoSearch">
                                                <template #option="option">
                                                    <div class="small">
                                                        <div class="font-weight-bold">@{{ option.nombre }}</div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">@{{
                                                            option.codigo_barras }}</div>
                                                    </div>
                                                </template>
                                            </v-select>
                                        </b-form-group>

                                        <!-- Cantidad y Añadir -->
                                        <div class="row align-items-end no-gutters mx-n1 mt-2">
                                            <div class="col-7 px-1">
                                                <b-form-group label="Cantidad:"
                                                    label-class="small font-weight-bold mb-1" class="mb-0">
                                                    <b-form-input v-model.number="form.cantidad" type="number" min="1"
                                                        size="sm" :state="cantidadValida"></b-form-input>
                                                </b-form-group>
                                            </div>
                                            <div class="col-5 px-1" v-if="form.tipo === 'transferencia'">
                                                <b-button variant="success" size="sm" block @click="agregarItem"
                                                    :disabled="!productoSeleccionado || !cantidadValida || form.cantidad < 1">
                                                    <i class="fas fa-plus mr-1"></i> Añadir
                                                </b-button>
                                            </div>
                                        </div>

                                        <!-- Estado de Stock en tiempo real -->
                                        <div class="mt-3 p-2 bg-white rounded border small" v-if="productoSeleccionado">
                                            <div class="d-flex justify-content-between mb-1"
                                                v-if="form.tipo !== 'ingreso'">
                                                <span class="text-muted">Stock Origen:</span>
                                                <span class="font-weight-bold">@{{ stockDisponible }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between" v-if="form.tipo !== 'salida'">
                                                <span class="text-success">Stock Destino:</span>
                                                <span class="text-success font-weight-bold">@{{ stockDestinoDisponible
                                                    }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between border-top pt-1 mt-1 font-italic"
                                                v-if="form.tipo !== 'ingreso'">
                                                <span>Disponible tras op.:</span>
                                                <span :class="stockRestante < 0 ? 'text-danger' : 'text-primary'"
                                                    class="font-weight-bold">@{{ stockRestante }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <!-- Configuración de Tiendas -->
                                    <div class="mb-3">
                                        <!-- Tienda Origen -->
                                        <div v-if="form.tipo !== 'ingreso'" class="mb-3">
                                            <label class="small font-weight-bold mb-2 uppercase text-muted">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Tienda Origen
                                            </label>
                                            <div class="row no-gutters mx-n1">
                                                <div v-for="tienda in tiendas" :key="'origen-'+tienda.id"
                                                    v-if="form.es_admin || form.tienda_id_usuario === tienda.id"
                                                    class="col-6 col-sm-4 col-md-3 col-lg-6 px-1 mb-2">
                                                    <div class="store-btn p-2 text-center h-100 d-flex flex-column justify-content-center"
                                                        :class="{ 
                                                            'active': form.tienda_origen_id === tienda.id
                                                        }" @click="form.tienda_origen_id = tienda.id">
                                                        <span class="store-info-label">ID: @{{ tienda.id }}</span>
                                                        <span class="store-name-label">@{{ tienda.nombre }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tienda Destino -->
                                        <div v-if="form.tipo !== 'salida'" class="mb-3">
                                            <label class="small font-weight-bold mb-2 uppercase text-success">
                                                <i class="fas fa-sign-in-alt mr-1"></i> Tienda Destino
                                            </label>
                                            <div class="row no-gutters mx-n1">
                                                <div v-for="tienda in tiendas" :key="'destino-'+tienda.id"
                                                    v-if="form.tienda_origen_id !== tienda.id"
                                                    class="col-6 col-sm-4 col-md-3 col-lg-6 px-1 mb-2">
                                                    <div class="store-btn p-2 text-center h-100 d-flex flex-column justify-content-center border-success-light"
                                                        :class="{ 
                                                            'active bg-success border-success': form.tienda_destino_id === tienda.id
                                                        }" @click="form.tienda_destino_id = tienda.id">
                                                        <span class="store-info-label">ID: @{{ tienda.id }}</span>
                                                        <span class="store-name-label">@{{ tienda.nombre }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Motivo de Ingreso (Solo para Ingresos) -->
                                    <div v-if="form.tipo === 'ingreso'" class="mb-3">
                                        <b-form-group label="Motivo de Ingreso:"
                                            label-class="small font-weight-bold mb-1">
                                            <b-form-select v-model="form.motivo" :options="opcionesMotivos" size="sm"
                                                required></b-form-select>
                                        </b-form-group>
                                    </div>

                                    <!-- Botón de Registro para Movimientos Simples -->
                                    <div v-if="form.tipo !== 'transferencia'" class="mt-4">
                                        <b-form-group label="Comentario:" label-class="small font-weight-bold mb-1">
                                            <b-form-textarea v-model="form.comentario" rows="2" size="sm"
                                                placeholder="Opcional..."></b-form-textarea>
                                        </b-form-group>
                                        <b-button type="submit" variant="primary" block size="lg"
                                            :disabled="loading || !productoSeleccionado || !cantidadValida">
                                            <b-spinner small v-if="loading"></b-spinner>
                                            <i class="fas fa-save mr-1"></i> Registrar
                                        </b-button>
                                    </div>
                                </div>

                                <!-- Columna Derecha (Solo Transferencia): Listado de Productos -->
                                <div class="col-lg-8" v-if="form.tipo === 'transferencia'">
                                    <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                                        <h6 class="text-primary mb-0 font-weight-bold small uppercase"><i
                                                class="fas fa-list-ul mr-1"></i> Detalle del Traslado Masivo</h6>
                                        <span class="badge badge-pill badge-primary">@{{ itemsTraslado.length }}
                                            productos seleccionados</span>
                                    </div>

                                    <div class="table-responsive border rounded bg-white shadow-xs"
                                        style="min-height: 280px; max-height: 400px;">
                                        <table class="table table-sm table-hover table-striped mb-0">
                                            <thead class="bg-light sticky-top shadow-sm">
                                                <tr class="small text-muted uppercase">
                                                    <th class="py-2 pl-3">Producto / Descripción</th>
                                                    <th class="text-center py-2" style="width: 140px;">Código</th>
                                                    <th class="text-center py-2" style="width: 100px;">Cantidad</th>
                                                    <th class="text-center py-2" style="width: 50px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="itemsTraslado.length === 0">
                                                    <td colspan="4" class="text-center py-5 text-muted">
                                                        <div class="mb-2"><i
                                                                class="fas fa-box-open fa-3x opacity-2"></i></div>
                                                        <p class="mb-0">No hay productos en la lista.</p>
                                                        <small>Use el panel de la izquierda para agregar
                                                            productos.</small>
                                                    </td>
                                                </tr>
                                                <tr v-for="(item, index) in itemsTraslado" :key="index">
                                                    <td class="align-middle pl-3">
                                                        <div class="font-weight-bold small text-dark">@{{ item.nombre }}
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle font-mono small">@{{
                                                        item.codigo_barras }}</td>
                                                    <td class="text-center align-middle font-weight-bold text-primary">
                                                        @{{ item.cantidad }}</td>
                                                    <td class="text-center align-middle">
                                                        <b-button variant="link" class="text-danger p-0 action-btn"
                                                            @click="quitarItem(index)">
                                                            <i class="fas fa-minus-circle"></i>
                                                        </b-button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row mt-4 align-items-end">
                                        <div class="col-md-7">
                                            <b-form-group label="Comentario del Traslado:"
                                                label-class="small font-weight-bold mb-1">
                                                <b-form-textarea v-model="form.comentario" rows="2" size="sm"
                                                    placeholder="Escriba aquí observaciones adicionales..."></b-form-textarea>
                                            </b-form-group>
                                        </div>
                                        <div class="col-md-5">
                                            <b-button type="submit" variant="success" size="lg" block
                                                :disabled="loading || itemsTraslado.length === 0" class="shadow-sm">
                                                <b-spinner small v-if="loading"></b-spinner>
                                                <i class="fas fa-check-double mr-1"></i> Procesar Traslado
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pantalla de Éxito Post-Operación -->
        <div class="row pt-4" v-if="ultimoTrasladoId">
            <div class="col-md-7 col-lg-5 mx-auto">
                <div class="card shadow border-0 text-center py-4 bg-white rounded-lg">
                    <div class="card-body px-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="font-weight-bold text-dark">¡Operación Completada!</h3>
                        <p class="text-muted lead small mb-4">El documento <strong>@{{ ultimoTrasladoCodigo }}</strong>
                            se ha registrado y el stock ha sido actualizado.</p>

                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-2">
                            <b-button :href="'/inventario/traslados/imprimir/' + ultimoTrasladoId" target="_blank"
                                variant="primary" size="lg" class="px-4 shadow-sm mb-2 mb-sm-0">
                                <i class="fas fa-print mr-2"></i> Imprimir Voucher
                            </b-button>
                            <b-button variant="outline-dark" size="lg" @click="nuevoTraslado" class="ml-sm-3 px-4">
                                <i class="fas fa-plus mr-2"></i> Nueva Operación
                            </b-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

<script>
    Vue.component('v-select', VueSelect.VueSelect);

    new Vue({
        el: '#transacciones-app',
        data() {
            return {
                loading: false,
                form: {
                    tipo: 'ingreso',
                    producto_id: null,
                    tienda_origen_id: null,
                    tienda_destino_id: null,
                    cantidad: 1,
                    motivo: 'compra',
                    comentario: '',
                    tienda_id_usuario: @json($tienda_id_usuario),
                    es_admin: @json($es_admin),
                    es_almacen: @json($es_almacen)
                },
                productoSeleccionado: null,
                productos: [],
                tiendas: [],
                stockDisponible: 0,
                stockDestinoDisponible: 0,
                loadingStock: false,
                itemsTraslado: [],
                ultimoTrasladoId: null,
                ultimoTrasladoCodigo: '',
                tiposMovimiento: [
                    { text: 'Ingreso', value: 'ingreso' },
                    { text: 'Salida', value: 'salida' },
                    { text: 'Transferencia', value: 'transferencia' }
                ],
                opcionesMotivos: [
                    { value: 'compra', text: 'Compra (Reposición)' },
                    { value: 'cambio', text: 'Cambio de Producto' },
                    { value: 'devolucion', text: 'Devolución de Cliente' }
                ]
            }
        },
        computed: {
            opcionesMovimientoFiltradas() {
                if (this.form.es_almacen) {
                    return [{ text: 'Transferencia', value: 'transferencia' }];
                }
                return this.tiposMovimiento;
            },
            opcionesTiendas() {
                return this.tiendas.map(t => ({ value: t.id, text: t.nombre }));
            },
            stockRestante() {
                if (this.form.tipo === 'ingreso') return null;
                // Si es transferencia, considerar lo ya agregado en la lista para ese mismo producto
                let yaAgregado = 0;
                if (this.form.tipo === 'transferencia' && this.form.producto_id) {
                    yaAgregado = this.itemsTraslado
                        .filter(i => i.producto_id === this.form.producto_id)
                        .reduce((sum, i) => sum + i.cantidad, 0);
                }
                return this.stockDisponible - yaAgregado - (this.form.cantidad || 0);
            },
            stockFinalDestino() {
                if (this.form.tipo === 'salida') return null;
                return this.stockDestinoDisponible + (this.form.cantidad || 0);
            },
            cantidadValida() {
                if (!this.productoSeleccionado) return true;
                if (this.form.tipo === 'ingreso') return true;
                if (!this.form.producto_id || !this.form.tienda_origen_id) return true;
                return this.stockRestante >= 0;
            }
        },
        watch: {
            productoSeleccionado(val) {
                this.form.producto_id = val ? val.id : null;
                this.checkStock();
                this.checkStockDestino();
            },
            'form.tienda_origen_id'(val) {
                this.checkStock();
            },
            'form.tienda_destino_id'(val) {
                this.checkStockDestino();
            },
            'form.tipo'(val) {
                this.itemsTraslado = []; // Resetear lista si cambia de tipo
                if (val === 'ingreso') {
                    this.stockDisponible = 0;
                    this.checkStockDestino();
                } else if (val === 'salida') {
                    this.stockDestinoDisponible = 0;
                    this.checkStock();
                } else {
                    this.checkStock();
                    this.checkStockDestino();
                }
                this.enfocarBuscador();
            }
        },
        mounted() {
            this.cargarTiendas();
            this.enfocarBuscador();
        },
        methods: {
            checkStock() {
                if (this.form.producto_id && this.form.tienda_origen_id && (this.form.tipo === 'salida' || this.form.tipo === 'transferencia')) {
                    this.loadingStock = true;
                    window.api.get(`/inventario/producto/stock/${this.form.producto_id}/${this.form.tienda_origen_id}`)
                        .then(res => {
                            this.stockDisponible = res.stock || 0;
                        })
                        .catch(err => {
                            console.error('Error consultando stock:', err);
                            this.stockDisponible = 0;
                        })
                        .finally(() => {
                            this.loadingStock = false;
                        });
                } else {
                    this.stockDisponible = 0;
                }
            },
            checkStockDestino() {
                if (this.form.producto_id && this.form.tienda_destino_id && (this.form.tipo === 'ingreso' || this.form.tipo === 'transferencia')) {
                    window.api.get(`/inventario/producto/stock/${this.form.producto_id}/${this.form.tienda_destino_id}`)
                        .then(res => {
                            this.stockDestinoDisponible = res.stock || 0;
                        })
                        .catch(err => {
                            console.error('Error consultando stock destino:', err);
                            this.stockDestinoDisponible = 0;
                        });
                } else {
                    this.stockDestinoDisponible = 0;
                }
            },
            cargarTiendas() {
                window.api.get('{{ route('inventario.salidas.tiendas.listado') }}')
                    .then(res => {
                        this.tiendas = res.data || res;

                        // Auto-selección de tienda origen basado en el usuario
                        if (this.form.tienda_id_usuario) {
                            this.form.tienda_origen_id = this.form.tienda_id_usuario;
                        }

                        if (this.form.es_almacen) {
                            this.form.tipo = 'transferencia';
                        }
                    });
            },
            onSearchProduct(search, loading) {
                if (search.length < 2) return;
                loading(true);
                window.api.post('/inventario/producto/buscar', { query: search })
                    .then(res => {
                        this.productos = Array.isArray(res) ? res : (res.data || []);
                        loading(false);
                    })
                    .catch(err => {
                        console.error('Error buscando productos:', err);
                        loading(false);
                    });
            },
            agregarItem() {
                if (!this.productoSeleccionado || !this.cantidadValida || this.form.cantidad < 1) return;

                let existente = this.itemsTraslado.find(i => i.producto_id === this.productoSeleccionado.id);
                if (existente) {
                    existente.cantidad += this.form.cantidad;
                } else {
                    this.itemsTraslado.push({
                        producto_id: this.productoSeleccionado.id,
                        nombre: this.productoSeleccionado.nombre,
                        codigo_barras: this.productoSeleccionado.codigo_barras,
                        cantidad: this.form.cantidad
                    });
                }

                this.productoSeleccionado = null;
                this.form.cantidad = 1;
                this.enfocarBuscador();
            },
            quitarItem(index) {
                this.itemsTraslado.splice(index, 1);
            },
            submitForm() {
                if (this.form.tipo === 'transferencia') {
                    this.enviarTrasladoMasivo();
                } else {
                    this.enviarMovimientoSimple();
                }
            },
            enviarMovimientoSimple() {
                if (!this.productoSeleccionado) {
                    this.$bvToast.toast('Por favor seleccione un producto.', { title: 'Validación', variant: 'warning', solid: true });
                    return;
                }
                this.loading = true;
                this.ultimoTrasladoId = null;
                window.api.post('{{ route('inventario.transacciones.store') }}', this.form)
                    .then(res => {
                        this.$bvToast.toast('Movimiento registrado con éxito.', { title: 'Éxito', variant: 'success', solid: true });
                        this.resetForm();
                    })
                    .catch(err => {
                        const msg = err.response?.data?.message || 'Error al registrar el movimiento.';
                        this.$bvToast.toast(msg, { title: 'Error', variant: 'danger', solid: true });
                    })
                    .finally(() => { this.loading = false; });
            },
            enviarTrasladoMasivo() {
                if (this.itemsTraslado.length === 0) return;
                if (this.form.tienda_origen_id === this.form.tienda_destino_id) {
                    this.$bvToast.toast('Misma tienda de origen y destino.', { title: 'Error', variant: 'danger', solid: true });
                    return;
                }
                this.loading = true;
                const payload = {
                    tienda_origen_id: this.form.tienda_origen_id,
                    tienda_destino_id: this.form.tienda_destino_id,
                    comentario: this.form.comentario,
                    items: this.itemsTraslado
                };
                window.api.post('{{ route('inventario.transacciones.traslado_masivo') }}', payload)
                    .then(res => {
                        this.ultimoTrasladoId = res.traslado_id;
                        this.ultimoTrasladoCodigo = res.codigo;
                        this.$bvToast.toast('Traslado procesado correctamente.', { title: 'Éxito', variant: 'success', solid: true });
                        this.resetForm();
                    })
                    .catch(err => {
                        const msg = err.response?.data?.message || 'Error al procesar el traslado.';
                        this.$bvToast.toast(msg, { title: 'Error', variant: 'danger', solid: true });
                    })
                    .finally(() => { this.loading = false; });
            },
            nuevoTraslado() {
                this.ultimoTrasladoId = null;
                this.resetForm();
            },
            resetForm() {
                const tipoActual = this.form.tipo;
                const origenId = this.form.tienda_origen_id;
                const destinoId = this.form.tienda_destino_id;
                const tiendaIdUsuario = this.form.tienda_id_usuario;
                const esAdmin = this.form.es_admin;
                const esAlmacen = this.form.es_almacen;
                this.form = {
                    tipo: esAlmacen ? 'transferencia' : tipoActual,
                    producto_id: null,
                    tienda_origen_id: origenId,
                    tienda_destino_id: destinoId,
                    cantidad: 1,
                    motivo: 'compra',
                    comentario: '',
                    tienda_id_usuario: tiendaIdUsuario,
                    es_admin: esAdmin,
                    es_almacen: esAlmacen
                };
                this.productoSeleccionado = null;
                this.itemsTraslado = [];
                this.stockDisponible = 0;
                this.stockDestinoDisponible = 0;
                this.enfocarBuscador();
            },
            enfocarBuscador() {
                this.$nextTick(() => {
                    if (this.$refs.productoSearch && this.$refs.productoSearch.$refs.search) {
                        this.$refs.productoSearch.$refs.search.focus();
                    }
                });
            }
        }
    });
</script>