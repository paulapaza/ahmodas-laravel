<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Traslados desde Almacén</x-slot>

    @push('styles')
    <style>
        :root {
            --xprimary: #4e73df;
            --xsuccess: #1cc88a;
            --xdanger: #e74a3b;
            --xwarning: #f6c23e;
            --xsecondary: #858796;
            --xdark: #3a3b45;
            --xlight: #f8f9fc;
        }
        .card-title.uppercase {
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .bg-xprimary { background-color: var(--xprimary); }
        .text-xprimary { color: var(--xprimary); }
        
        /* Utilidades */
        .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important; }
        .table-sm-text { font-size: 0.85rem; }
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* Modales */
        .modal-header.bg-xprimary, .modal-header.bg-success, .modal-header.bg-danger, .modal-header.bg-secondary {
            padding: 0.5rem 1rem !important;
            color: white;
            align-items: center;
            border-bottom: 0;
        }
        .modal-title { font-size: 0.9rem !important; font-weight: 700; text-transform: uppercase; }
        .modal-header .close { color: white; opacity: 0.8; margin-top: -10px; }

        /* Tiendas Grid */
        .store-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 5px;
        }
        .store-box {
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            background: white;
            position: relative;
            height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .store-box:hover { border-color: var(--xprimary); background-color: var(--xlight); transform: translateY(-2px); }
        .store-box.active { border-color: var(--xprimary); background-color: #eaecf4; box-shadow: 0 4px 10px rgba(78, 115, 223, 0.15); }
        .store-box .check-icon { display: none; position: absolute; top: 8px; right: 8px; color: var(--xprimary); font-size: 0.85rem; }
        .store-box.active .check-icon { display: block; }
        .store-box .store-name { font-size: 0.8rem; font-weight: 800; color: var(--xdark); margin-top: 4px; text-transform: uppercase; }
        .store-box.active .store-name { color: var(--xprimary); }
        .store-box i.main-icon { font-size: 1.5rem; color: #b7b9cc; }
        .store-box.active i.main-icon { color: var(--xprimary); }
    </style>
    @endpush

    <div id="traslados-almacen-app" v-cloak>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- Sección Izquierda: Realizar Traslado -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-xprimary text-white py-3">
                            <h6 class="card-title mb-0 uppercase">
                                <i class="fas fa-paper-plane mr-2"></i> Realizar Traslado
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="#">
                                <div class="form-group mb-4">
                                    <label class="small font-weight-bold mb-2 d-block text-xprimary">1. TIENDA DESTINO:</label>
                                    <div class="store-grid">
                                        <div v-for="tienda in tiendas" :key="tienda.id" 
                                             class="store-box" 
                                             :class="{ 'active': form.tiendaId === tienda.id }"
                                             @click="form.tiendaId = tienda.id">
                                            <div class="store-name">@{{ tienda.nombre }}</div>
                                            <div v-if="form.productoId && form.tiendaId === tienda.id" class="small text-muted font-weight-bold mt-1">
                                                Stock: @{{ getStockEnTienda(tienda.id) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="small font-weight-bold text-xprimary">2. PRODUCTO EN ALMACÉN:</label>
                                    <select class="form-control form-control-lg shadow-sm" v-model="form.productoId" :disabled="cargando" style="font-size: 0.9rem;">
                                        <option value="">@{{ cargando ? 'Cargando catálogo...' : 'Buscar producto...' }}</option>
                                        <option v-for="prod in listaProductos" :key="prod.id" :value="prod.id">
                                            @{{ prod.nombre }}
                                        </option>
                                    </select>
                                    <div v-if="productoSeleccionadoDetalle" 
                                         class="mt-1 small font-weight-bold"
                                         :class="productoSeleccionadoDetalle.stock <= 0 ? 'text-danger' : 'text-muted'">
                                        Stock: @{{ productoSeleccionadoDetalle.stock }}
                                        <span v-if="productoSeleccionadoDetalle.stock <= 0"> [AGOTADO EN ALMACÉN]</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-xprimary">3. CANTIDAD A TRASLADAR:</label>
                                    <input type="number" 
                                           class="form-control form-control-lg text-center font-weight-bold" 
                                           v-model.number="form.cantidad" 
                                           min="1"
                                           :disabled="!form.productoId || (productoSeleccionadoDetalle && productoSeleccionadoDetalle.stock <= 0)">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sección Derecha: Productos en Tiendas -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6 d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0 text-primary uppercase">
                                        <i class="fas fa-store mr-2"></i> Productos en Tiendas
                                    </h6>
                                </div>
                                <!-- Botón Historial -->
                                <div class="col-md-6 text-right">
                                    <a href="{{ route('inventario.traslados_almacen.historial') }}" class="btn btn-outline-primary btn-sm shadow-xs">
                                        <i class="fas fa-history mr-1"></i> Historial de Traslados
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <!-- Filtros -->
                                <div class="col-12">
                                    <div class="row no-gutters">
                                        <div class="col-6 pr-1">
                                            <select class="form-control form-control-sm" name="filter_tienda">
                                                <option value="">Todas las tiendas</option>
                                            </select>
                                        </div>
                                        <div class="col-6 pl-1">
                                            <input type="text" class="form-control form-control-sm" placeholder="Buscar producto...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm mb-0 table-sm-text">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="pl-3 py-3 border-top-0">Producto</th>
                                            <th class="py-3 border-top-0">Tienda</th>
                                            <th class="py-3 border-top-0">Fecha y Hora</th>
                                            <th class="py-3 border-top-0 text-center">Vendido</th>
                                            <th class="py-3 border-top-0 text-center">Disponible</th>
                                            <th class="py-3 border-top-0 text-center text-muted">En Almacén</th>
                                            <th class="text-right py-3 border-top-0">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="traslado in trasladosEnCurso" :key="`${traslado.id}-${traslado.tienda_id}`">
                                        <td class="pl-3 align-middle">
                                            <div class="font-weight-bold">@{{ traslado.nombre }}</div>
                                            <div class="small text-muted">Código: @{{ traslado.codigo || 'S/C' }}</div>
                                        </td>
                                        <td class="align-middle small">@{{ traslado.tienda_nombre }}</td>
                                        <td class="align-middle small font-weight-bold">@{{ traslado.fecha }}</td>
                                        <td class="align-middle text-center">@{{ traslado.vendido }}</td>
                                        <td class="align-middle text-center text-primary font-weight-bold">@{{ traslado.disponible }}</td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 0.85rem;">
                                                @{{ getStockAlmacenReal(traslado.id) }} 
                                                @{{ getStockAlmacenReal(traslado.id) === 1 ? 'unidad' : 'unidades' }}
                                            </span>
                                        </td>
                                        <td class="text-right align-middle">
                                            <!-- Grupo 1: Operaciones (Confirmar/Borrar o Venta/Devolución) -->
                                            <div class="btn-group btn-group-sm ml-2" v-if="!traslado.confirmado">
                                                <button @click="confirmarItem(traslado)" type="button" class="btn btn-primary btn-sm" v-b-tooltip.hover title="Confirmar e ingresar a tienda">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button @click="quitarTraslado(traslado)" type="button" class="btn btn-outline-danger btn-sm" v-b-tooltip.hover title="Quitar de la lista">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="btn-group btn-group-sm ml-2" v-else>
                                                <button @click="abrirVender(traslado)" type="button" class="btn btn-outline-success btn-sm" v-b-tooltip.hover title="Marcar como vendido">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                                <button @click="abrirDevolver(traslado)" type="button" class="btn btn-outline-danger btn-sm" v-b-tooltip.hover title="Regresar stock">
                                                    <i class="fas fa-truck-moving"></i>
                                                </button>
                                            </div>

                                            <!-- Grupo 2: Gestión (Historial / Editar) -->
                                            <div class="btn-group btn-group-sm ml-2" v-if="traslado.confirmado">
                                                <button @click="verHistorialProducto(traslado)" type="button" class="btn btn-outline-info btn-sm" v-b-tooltip.hover title="Ver historial">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                                <button @click="abrirEditar(traslado)" type="button" class="btn btn-outline-success btn-sm" v-b-tooltip.hover title="Cargar más stock">
                                                    <i class="fas fa-truck-loading"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="trasladosEnCurso.length === 0">
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            Seleccione un producto para previsualizar el traslado
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-3">
                            <div class="row align-items-center">
                                <div class="col-md-12 text-right" v-if="hayTrasladosPendientes">
                                    <button @click="confirmarTodo" class="btn btn-primary btn-sm shadow-sm" :disabled="cargando">
                                        <i v-if="cargando" class="fas fa-spinner fa-spin mr-1"></i>
                                        <i v-else class="fas fa-check-double mr-1"></i> 
                                        @{{ cargando ? 'Procesando...' : 'Confirmar Todos los Traslados' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Historial de Producto -->
        <b-modal id="modal-historial-producto" title="HISTORIAL DE CAMBIOS" hide-footer header-class="bg-info text-white">
            <div class="p-2">
                <h6 class="font-weight-bold truncate mb-3 text-center">@{{ productoSeleccionado }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover border">
                        <thead class="bg-light text-center small uppercase">
                            <tr>
                                <th class="py-2">Fecha y Hora</th>
                                <th class="py-2">Vendido</th>
                                <th class="py-2">Disponible</th>
                                <th class="py-2 text-muted">Stock Almacén</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <tr v-for="(item, index) in historialProducto" :key="index">
                                <td class="align-middle small">@{{ item.fecha_formateada }}</td>
                                <td class="align-middle">@{{ item.vendido }}</td>
                                <td class="align-middle font-weight-bold text-primary">@{{ item.disponible }}</td>
                                <td class="align-middle text-muted">@{{ item.almacen }} @{{ item.almacen === 1 ? 'unidad' : 'unidades' }}</td>
                            </tr>
                            <tr v-if="historialProducto.length === 0">
                                <td colspan="4" class="py-4 text-muted small">No hay movimientos registrados para este ítem todavía.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </b-modal>



        <!-- Modal: Editar Traslado (Carga Adicional) -->
        <b-modal id="modal-editar-traslado" title="CARGA ADICIONAL DESDE ALMACÉN" size="sm" hide-footer header-class="bg-success text-white">
            <div class="p-3 text-center" v-if="itemParaEditar">
                <h6 class="font-weight-bold truncate mb-0">@{{ itemParaEditar.nombre }}</h6>
                <div class="small text-muted mb-2">
                    Disponible en Almacén: <span class="font-weight-bold">@{{ getStockAlmacenReal(itemParaEditar.id) }} @{{ getStockAlmacenReal(itemParaEditar.id) === 1 ? 'unidad' : 'unidades' }}</span>
                </div>
                <div class="small text-muted mb-1">Cantidad a enviar adicional:</div>
                <div class="d-flex justify-content-center align-items-center">
                    <input type="number" v-model.number="nuevaCantidadEdicion" class="form-control mr-1 text-center font-weight-bold" style="width: 100px" min="1">
                    <button @click="guardarEdicion" class="btn btn-success" :disabled="cargando">
                        <i class="fas" :class="cargando ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                    </button>
                </div>

                <hr class="my-4">

                <!-- Opción de Eliminación -->
                <button @click="eliminarTrasladoConfirmado" class="btn btn-link btn-sm text-danger btn-block p-0">
                   <i class="fas fa-trash-alt mr-1"></i> Eliminar y regresar todo al almacén
                </button>
            </div>
        </b-modal>

        <!-- Modal: Vender Producto -->
        <b-modal id="modal-vender" title="MARCAR COMO VENDIDO" size="sm" hide-footer header-class="bg-success text-white">
            <div class="p-3 text-center" v-if="itemParaOperar">
                <h6 class="font-weight-bold truncate mb-0">@{{ itemParaOperar.nombre }}</h6>
                <div class="small text-muted mb-2">
                    Disponible en Tienda: <span class="font-weight-bold">@{{ itemParaOperar.disponible }} @{{ itemParaOperar.disponible === 1 ? 'unidad' : 'unidades' }}</span>
                </div>
                <div class="small text-muted mb-1">Cantidad a marcar como vendida:</div>
                <div class="d-flex justify-content-center align-items-center">
                    <input type="number" v-model.number="form.cantidad" class="form-control mr-1 text-center font-weight-bold" style="width: 100px" min="0">
                    <button @click="procesarOperacion('Venta')" class="btn btn-success" :disabled="cargando">
                        <i class="fas" :class="cargando ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                    </button>
                </div>
            </div>
        </b-modal>

        <!-- Modal: Devolver Producto -->
        <b-modal id="modal-devolver" title="REGRESAR STOCK AL ALMACÉN" size="sm" hide-footer header-class="bg-danger text-white">
            <div class="p-3 text-center" v-if="itemParaOperar">
                <h6 class="font-weight-bold truncate mb-0">@{{ itemParaOperar.nombre }}</h6>
                <div class="small text-muted mb-2">
                    Disponible en Tienda: <span class="font-weight-bold">@{{ itemParaOperar.disponible }} @{{ itemParaOperar.disponible === 1 ? 'unidad' : 'unidades' }}</span>
                </div>
                <div class="small text-muted mb-1">Cantidad a regresar al almacén:</div>
                <div class="d-flex justify-content-center align-items-center">
                    <input type="number" v-model.number="form.cantidad" class="form-control mr-1 text-center font-weight-bold" style="width: 100px" min="1" :max="itemParaOperar.disponible">
                    <button @click="procesarOperacion('Devolución')" class="btn btn-danger" :disabled="cargando">
                        <i class="fas" :class="cargando ? 'fa-spinner fa-spin' : 'fa-undo-alt'"></i>
                    </button>
                </div>
            </div>
        </b-modal>
    </div>

    @push('scripts')
    <script>
        Vue.use(BootstrapVue);

        new Vue({
            el: '#traslados-almacen-app',
            data() {
                return {
                    // Estado del Formulario Principal
                    form: {
                        productoId: '',
                        cantidad: 1,
                        tiendaId: ''
                    },
                    // Datos Maestros (API)
                    listaProductos: [],
                    tiendas: [],
                    stockMap: {},
                    cargando: true,
                    
                    // Estado de la Tabla y Traslados
                    trasladosEnCurso: [],
                    
                    // Estado de Modales y Edición
                    productoSeleccionado: '', // Nombre para modales informativos
                    itemParaEditar: null,
                    nuevaCantidadEdicion: 0,
                    puntoSeleccionado: {},
                    itemParaOperar: null, // Objeto de traslado para Venta/Devolución
                    historialProducto: []
                }
            },
            watch: {
                'form.productoId'(newVal) {
                    if (newVal) {
                        const prod = this.listaProductos.find(p => p.id === newVal);
                        this.form.cantidad = (prod && prod.stock > 0) ? 1 : 0;
                        this.sincronizarTraslado();
                    }
                },
                'form.cantidad'(newVal) {
                    if (this.form.productoId) {
                        this.sincronizarTraslado(true);
                    }
                },
                trasladosEnCurso: {
                    deep: true,
                    handler(newVal) {
                        // Solo persistimos localmente aquellos que NO están confirmados (borradores)
                        const pendientes = newVal.filter(t => !t.confirmado);
                        localStorage.setItem('traslados_pendientes', JSON.stringify(pendientes));
                    }
                }
            },
            mounted() {
                // Cargar datos persistidos si existen
                const saved = localStorage.getItem('traslados_pendientes');
                if (saved) {
                    try {
                        this.trasladosEnCurso = JSON.parse(saved);
                    } catch (e) {
                        console.error("Error al cargar traslados guardados", e);
                        localStorage.removeItem('traslados_pendientes');
                    }
                }
                this.fetchProductos();
            },
            computed: {
                productoSeleccionadoDetalle() {
                    if (!this.form.productoId) return null;
                    return this.listaProductos.find(p => p.id === this.form.productoId);
                },
                hayTrasladosPendientes() {
                    return this.trasladosEnCurso.some(t => !t.confirmado);
                }
            },
            methods: {
                // --- HELPERS ATÓMICOS ---
                toast(msg, title = 'Aviso', variant = 'info') {
                    this.$bvToast.toast(msg, { title, variant, solid: true });
                },
                validarStockDisponible(productoId, cantidad) {
                    const prod = this.listaProductos.find(p => p.id === productoId);
                    if (!prod) return { ok: false, msg: 'Producto no encontrado' };
                    if (prod.stock <= 0) return { ok: false, msg: `El producto ${prod.nombre} no tiene existencias.` };
                    if (cantidad > prod.stock) return { ok: false, msg: `Solo hay ${prod.stock} unidades disponibles.`, max: prod.stock };
                    return { ok: true, stock: prod.stock };
                },

                // --- GESTIÓN DE UI ---
                getStockEnTienda(tiendaId) {
                    if (!this.form.productoId) return 0;
                    return (this.stockMap[this.form.productoId] && this.stockMap[this.form.productoId][tiendaId]) || 0;
                },
                async verHistorialProducto(traslado) {
                    this.productoSeleccionado = traslado.nombre;
                    this.historialProducto = [];
                    this.$bvModal.show('modal-historial-producto');

                    try {
                        const response = await axios.get('/api/inventario/traslados/historial-datos', {
                            params: { traslado_id: traslado.traslado_id }
                        });
                        this.historialProducto = response.data.historial;
                    } catch (error) {
                        this.toast('Error al cargar el historial.', 'Error', 'danger');
                    }
                },
                abrirVender(traslado) {
                    this.itemParaOperar = traslado;
                    this.form.cantidad = traslado.vendido; // Cargamos el total actual
                    this.$bvModal.show('modal-vender');
                },
                abrirDevolver(traslado) {
                    this.itemParaOperar = traslado;
                    this.form.cantidad = 1; // Unidades a regresar
                    this.$bvModal.show('modal-devolver');
                },

                procesarOperacion(tipo) {
                    if (!this.itemParaOperar) return;

                    const url = tipo === 'Venta' 
                        ? '/api/inventario/traslados/actualizar-venta' 
                        : '/api/inventario/traslados/actualizar-devolucion';

                    const payload = {
                        traslado_id: this.itemParaOperar.traslado_id,
                        [tipo === 'Venta' ? 'nueva_venta' : 'cantidad_a_regresar']: this.form.cantidad
                    };

                    this.cargando = true;
                    axios.post(url, payload)
                        .then(response => {
                            if (response.data.success) {
                                this.toast(`${tipo} actualizada correctamente.`, 'Éxito', 'success');
                                this.$bvModal.hide('modal-vender');
                                this.$bvModal.hide('modal-devolver');
                                this.fetchProductos(); // Recargar todo el estado (stocks, disponibles, etc)
                            }
                        })
                        .catch(error => {
                            this.toast(error.response?.data?.message || 'Error al procesar la operación', 'Error', 'danger');
                        })
                        .finally(() => {
                            this.cargando = false;
                        });
                },

                async fetchProductos() {
                    try {
                        const response = await axios.get('/api/inventario/traslados/datos-gestion');
                        this.listaProductos = response.data.productos;
                        this.stockMap = response.data.stockMap;
                        this.tiendas = response.data.tiendas;

                        // --- FUSIÓN DE DATOS (BD + LOCAL) ---
                        const confirmados = response.data.confirmados || [];
                        const pendientesLocal = this.trasladosEnCurso.filter(t => !t.confirmado);
                        
                        // Evitamos duplicidad si un (ID, TIENDA) ya se guardó en BD
                        // Usamos llaves compuestas para comparar de forma precisa
                        const keysConfirmados = confirmados.map(c => `${c.id}-${c.tienda_id}`);
                        const borradoresSinDuplicados = pendientesLocal.filter(p => {
                            const keyP = `${p.id}-${p.tienda_id}`;
                            return !keysConfirmados.includes(keyP);
                        });

                        // Unimos: Primero confirmados, luego borradores
                        this.trasladosEnCurso = [...confirmados, ...borradoresSinDuplicados];
                        // -------------------------------------

                        if (this.tiendas.length > 0 && !this.form.tiendaId) {
                            this.form.tiendaId = this.tiendas[0].id;
                        }
                    } catch (error) {
                        console.error('Error al cargar productos:', error);
                        this.toast('Error al cargar el listado de productos', 'Error API', 'danger');
                    } finally {
                        this.cargando = false;
                    }
                },
                sincronizarTraslado(esUpdateExplicit = false) {
                    const validation = this.validarStockDisponible(this.form.productoId, this.form.cantidad);
                    
                    if (!validation.ok) {
                        // Solo mostramos toast si hay algo de stock pero es insuficiente
                        if (validation.stock > 0) {
                            this.toast(validation.msg, 'Stock Insuficiente', 'warning');
                        }

                        if (validation.stock === 0) {
                            this.form.cantidad = 0;
                        } else if (validation.max) {
                            this.form.cantidad = validation.max;
                        }

                        // Si la cantidad es 0 (agotado), detenemos el proceso inmediatamente
                        if (this.form.cantidad <= 0 || !esUpdateExplicit) return;
                    }

                    const prod = this.productoSeleccionadoDetalle;
                    // Buscamos si ya existe para ESTA tienda específica
                    const index = this.trasladosEnCurso.findIndex(t => t.id === prod.id && t.tienda_id === this.form.tiendaId);
                    const tienda = this.tiendas.find(t => t.id === this.form.tiendaId);

                    if (index !== -1) {
                        if (this.trasladosEnCurso[index].confirmado) {
                            this.toast(`El producto ${prod.nombre} ya fue enviado. Usa 'Editar' para ajustar.`, 'Aviso', 'info');
                            this.form.productoId = '';
                            this.form.cantidad = 1;
                            return;
                        }

                        if (esUpdateExplicit) {
                            this.trasladosEnCurso[index].disponible = this.form.cantidad;
                        } else {
                            if (this.trasladosEnCurso[index].disponible + 1 <= validation.stock) {
                                this.trasladosEnCurso[index].disponible += 1;
                                this.form.cantidad = this.trasladosEnCurso[index].disponible;
                            } else {
                                this.toast('Límite de stock alcanzado.', 'Límite', 'warning');
                            }
                        }
                    } else {
                        this.trasladosEnCurso.push({
                            id: prod.id,
                            nombre: prod.nombre,
                            codigo: prod.codigo,
                            tienda_id: this.form.tiendaId,
                            tienda_nombre: tienda ? tienda.nombre : 'Sin Tienda',
                            fecha: new Date().toLocaleString(),
                            vendido: 0,
                            devuelto: 0,
                            disponible: this.form.cantidad,
                            confirmado: false
                        });
                    }
                },
                confirmarItem(traslado) {
                    traslado.confirmado = true;
                    this.toast(`Traslado de ${traslado.nombre} validado.`, 'Confirmado', 'primary');
                },
                confirmarTodo() {
                    const pendientes = this.trasladosEnCurso.filter(t => !t.confirmado);
                    if (pendientes.length === 0) return;
                    
                    // --- PREPARACIÓN DE DATOS PARA ENDPOINT (SIMULACIÓN) ---
                    const datosParaEnviar = pendientes.map(t => {
                        const stockAlmacenAnterior = this.getStockAlmacenReal(t.id);
                        const stockTiendaAnterior = (this.stockMap[t.id] && this.stockMap[t.id][this.form.tiendaId]) || 0;
                        
                        return {
                            almacen_id: 1, // Almacén Principal
                            tienda_id: this.form.tiendaId,
                            producto_id: t.id,
                            almacen_stock_anterior: stockAlmacenAnterior,
                            tienda_stock_anterior: stockTiendaAnterior,
                            almacen_stock_posterior: stockAlmacenAnterior - t.disponible,
                            tienda_stock_posterior: stockTiendaAnterior + t.disponible,
                            stock_vendido: t.vendido,
                            stock_devuelto: t.devuelto,
                            stock_disponible: t.disponible,
                        };
                    });

                    // --- ENVÍO REAL AL ENDPOINT ---
                    this.cargando = true;
                    axios.post('/api/inventario/traslados/guardar', { traslados: datosParaEnviar })
                        .then(response => {
                            if (response.data.success) {
                                // Marcamos como confirmados localmente
                                pendientes.forEach(t => t.confirmado = true);
                                
                                // Limpieza de UI
                                this.form.productoId = '';
                                this.form.cantidad = 1;
                                
                                this.toast(response.data.message, 'Éxito', 'success');
                                
                                // Opcional: Recargar datos para ver stocks actualizados
                                this.fetchProductos();
                            }
                        })
                        .catch(error => {
                            const msg = error.response?.data?.message || 'Error al procesar la solicitud';
                            this.toast(msg, 'Error de Guardado', 'danger');
                        })
                        .finally(() => {
                            this.cargando = false;
                        });
                    // ---------------------------------------------------------
                },
                quitarTraslado(item) {
                    this.trasladosEnCurso = this.trasladosEnCurso.filter(t => t !== item);
                    // Si el producto que estamos quitando es el que está seleccionado en el buscador, lo limpiamos
                    if (this.form.productoId === item.id && this.form.tiendaId === item.tienda_id) {
                        this.form.productoId = '';
                    }
                },
                abrirEditar(item) {
                    this.itemParaEditar = item;
                    this.nuevaCantidadEdicion = 1; // Unidades a agregar
                    this.$bvModal.show('modal-editar-traslado');
                },
                guardarEdicion() {
                    // --- ACTUALIZACIÓN REAL EN BD (AGREGAR) ---
                    this.cargando = true;
                    axios.post('/api/inventario/traslados/actualizar-stock', {
                        traslado_id: this.itemParaEditar.traslado_id,
                        cantidad_agregada: this.nuevaCantidadEdicion
                    })
                    .then(response => {
                        if (response.data.success) {
                            this.toast(response.data.message, 'Éxito', 'success');
                            this.$bvModal.hide('modal-editar-traslado');
                            this.fetchProductos(); 
                        }
                    })
                    .catch(error => {
                        this.toast(error.response?.data?.message || 'Error al actualizar', 'Error', 'danger');
                    })
                    .finally(() => this.cargando = false);
                },
                eliminarTrasladoConfirmado() {
                    const msg = this.itemParaEditar.confirmado 
                        ? '¿Eliminar traslado y DEVOLVER stock al almacén?' 
                        : '¿Quitar este ítem de la lista?';

                    if (confirm(msg)) {
                        if (this.itemParaEditar.confirmado) {
                            this.cargando = true;
                            axios.post('/api/inventario/traslados/eliminar', {
                                traslado_id: this.itemParaEditar.traslado_id
                            })
                            .then(response => {
                                if (response.data.success) {
                                    this.trasladosEnCurso = this.trasladosEnCurso.filter(t => t !== this.itemParaEditar);
                                    this.toast('Registro eliminado y stock devuelto.', 'Éxito', 'danger');
                                    this.$bvModal.hide('modal-editar-traslado');
                                    this.fetchProductos();
                                }
                            })
                            .catch(error => {
                                this.toast(error.response?.data?.message || 'Error al eliminar', 'Error', 'danger');
                            })
                            .finally(() => this.cargando = false);
                        } else {
                            this.quitarTraslado(this.itemParaEditar);
                            this.$bvModal.hide('modal-editar-traslado');
                        }
                    }
                },
                getStockAlmacenReal(productoId) {
                    const prod = this.listaProductos.find(p => p.id === productoId);
                    return prod ? prod.stock : 0;
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
