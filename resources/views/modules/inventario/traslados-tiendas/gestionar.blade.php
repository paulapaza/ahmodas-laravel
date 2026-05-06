<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Traslados hacia Almacén</x-slot>

    @push('styles')
    <style>
        :root {
            --xprimary: #1cc88a; /* Verde para diferenciar del azul del almacén */
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
        
        .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important; }
        .table-sm-text { font-size: 0.85rem; }
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* Tiendas Grid */
        .store-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
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
        .store-box.active { border-color: var(--xprimary); background-color: #f0fff4; box-shadow: 0 4px 10px rgba(28, 200, 138, 0.15); }
        .store-box .check-icon { display: none; position: absolute; top: 8px; right: 8px; color: var(--xprimary); font-size: 0.85rem; }
        .store-box.active .check-icon { display: block; }
        .store-box .store-name { font-size: 0.8rem; font-weight: 800; color: var(--xdark); margin-top: 4px; text-transform: uppercase; }
        .store-box.active .store-name { color: var(--xprimary); }

        /* Buscador de Productos Dinámico */
        .search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e3e6f0;
            border-radius: 0 0 10px 10px;
            z-index: 1050;
            max-height: 280px;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-top: none;
        }
        .search-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fc;
            transition: all 0.2s;
            font-size: 0.85rem;
        }
        .search-item:hover { background: #f0fff4; color: var(--xprimary); padding-left: 20px; }
        .search-item:last-child { border-bottom: none; border-radius: 0 0 10px 10px; }
        .search-item .item-code { font-size: 0.75rem; font-weight: 700; color: #b7b9cc; }

        /* Animación de resta de stock */
        .stock-diff-anim {
            animation: fadeInOut 1s ease-in-out;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-5px); }
            50% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0.7; }
        }
        /* Estilos de la tabla */
        .store-badge {
            font-size: 0.95rem;
            padding: 5px 10px;
            font-weight: 700;
        }
    </style>
    @endpush

    <div id="traslados-tiendas-app" v-cloak>
        <div class="container-fluid py-4">
            <div class="row">
                <!-- Sección Izquierda: Realizar Traslado -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-xprimary text-white py-3">
                            <h6 class="card-title mb-0 uppercase">
                                <i class="fas fa-truck-loading mr-2"></i> Realizar Traslado
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="#" @submit.prevent>
                                <!-- 1. Seleccionar Tienda -->
                                <div class="form-group mb-4">
                                    <label class="small font-weight-bold text-xprimary">1. TIENDA ORIGEN:</label>
                                    <div class="store-grid">
                                        <div v-for="tienda in tiendas" :key="tienda.id" 
                                             class="store-box" 
                                             :class="{ 'active': form.tiendaId === tienda.id }"
                                             @click="seleccionarTienda(tienda.id)">
                                            <div class="store-name">@{{ tienda.alias || tienda.nombre }}</div>
                                            <i class="fas fa-check-circle check-icon"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Buscar Producto -->
                                <div class="form-group mb-4" v-if="form.tiendaId">
                                    <label class="small font-weight-bold text-xprimary">2. PRODUCTO EN TIENDA:</label>
                                    <div class="position-relative">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                            </div>
                                            <input type="text" 
                                                   class="form-control form-control-lg border-left-0 shadow-sm" 
                                                   :placeholder="cargandoProductos ? 'Cargando productos...' : 'Busque por nombre o código...'"
                                                   v-model="busquedaSelector"
                                                   @focus="dropdownAbierto = true"
                                                   @blur="cerrarDropdownDespues"
                                                   :disabled="cargandoProductos"
                                                   style="font-size: 0.9rem;">
                                            <div class="input-group-append" v-if="form.productoId">
                                                <button class="btn btn-outline-secondary border-left-0" type="button" @click="limpiarSeleccionProducto">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Lista de Sugerencias -->
                                        <div v-if="dropdownAbierto && listaSugerencias.length > 0" class="search-dropdown">
                                            <div v-for="prod in listaSugerencias" 
                                                 :key="prod.id" 
                                                 class="search-item d-flex justify-content-between align-items-center"
                                                 @mousedown="seleccionarProducto(prod)">
                                                <div>
                                                    <div class="font-weight-bold text-dark">@{{ prod.alias || prod.nombre }}</div>
                                                    <div class="item-code uppercase">Código: @{{ prod.codigo || 'S/C' }}</div>
                                                </div>
                                                <span class="badge badge-light border text-muted">Stock: @{{ prod.stock_tienda }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="productoSeleccionadoDetalle" class="mt-2 p-2 bg-light rounded small border">
                                        <div class="d-flex justify-content-between">
                                            <span>Disponible en tienda:</span>
                                            <span class="font-weight-bold text-success">@{{ productoSeleccionadoDetalle.stock_tienda }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Actual en Almacén:</span>
                                            <span class="font-weight-bold text-primary">@{{ productoSeleccionadoDetalle.stock_almacen }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Cantidad -->
                                <div class="form-group mb-4" v-if="form.productoId">
                                    <label class="small font-weight-bold text-xprimary">3. CANTIDAD A TRASLADAR:</label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary" type="button" @click="cambiarCantidad(-1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                        <input type="number" 
                                               class="form-control text-center font-weight-bold" 
                                               v-model.number="form.cantidad" 
                                               min="1"
                                               :max="productoSeleccionadoDetalle ? productoSeleccionadoDetalle.stock_tienda : 1">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" @click="cambiarCantidad(1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0" v-if="hayTrasladosPendientes">
                                    <button @click="procesarTraslados" class="btn btn-primary btn-lg shadow-sm w-100" :disabled="cargando">
                                        <i v-if="cargando" class="fas fa-spinner fa-spin mr-1"></i>
                                        <i v-else class="fas fa-check-double mr-1"></i>
                                        @{{ cargando ? 'Procesando...' : 'Confirmar Traslados' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sección Derecha: Vista Previa y Tabla -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="card-title mb-0 text-primary uppercase">
                                        <i class="fas fa-list-alt mr-2"></i> Traslados Realizados
                                    </h6>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="{{ route('inventario.traslados_tiendas.historial') }}" class="btn btn-outline-primary btn-sm shadow-xs">
                                        <i class="fas fa-history mr-1"></i> Ver Historial Completo
                                    </a>
                                </div>
                            </div>
                            <!-- Filtros de la Tabla -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="row no-gutters bg-light p-1 rounded border shadow-xs" style="backdrop-filter: blur(5px); background: rgba(248, 249, 250, 0.85) !important;">
                                        <div class="col pr-1">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-store text-muted small"></i></span>
                                                </div>
                                                <select class="form-control form-control-sm border-left-0" v-model="filtroTienda">
                                                    <option value="">Todas las tiendas</option>
                                                    <option v-for="t in tiendas" :key="t.id" :value="t.id">@{{ t.alias || t.nombre }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col px-1">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted small"></i></span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm border-left-0" placeholder="Buscar producto..." v-model="filtroProducto">
                                            </div>
                                        </div>
                                        <div class="col pl-1">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt text-muted small"></i></span>
                                                </div>
                                                <input type="date" class="form-control form-control-sm border-left-0" v-model="filtroFecha">
                                            </div>
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
                                            <th class="pl-3 py-3 border-top-0">#</th>
                                            <th class="py-3 border-top-0">Tienda</th>
                                            <th class="py-3 border-top-0">Producto</th>
                                            <th class="py-3 border-top-0 text-center">Fecha</th>
                                            <th class="py-3 border-top-0 text-center">Stock Tienda</th>
                                            <th class="py-3 border-top-0 text-center">Stock Almacén</th>
                                            <th class="text-right py-3 border-top-0 pr-3">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(t, index) in trasladosFiltrados" :key="t.traslado_id || `pend-${index}`">
                                            <td class="pl-3 align-middle">@{{ index + 1 }}</td>
                                            <td class="align-middle font-weight-bold text-dark">
                                                <span class="badge badge-light border store-badge">@{{ t.tienda_alias || t.tienda_nombre }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold">@{{ t.producto_alias || t.producto_nombre || t.nombre }}</div>
                                                <div class="small text-muted">@{{ t.producto_codigo || t.codigo }}</div>
                                            </td>
                                            <td class="align-middle text-center small">@{{ t.confirmado ? formatearFecha(t.fecha) : t.fecha_visual }}</td>
                                            <td class="align-middle text-center">
                                                <div v-if="!t.confirmado">
                                                    <div class="font-weight-bold text-danger">@{{ t.tienda_stock_anterior }} - @{{ t.cantidad }}</div>
                                                    <div class="small font-weight-bold text-muted stock-diff-anim">= @{{ t.tienda_stock_anterior - t.cantidad }}</div>
                                                </div>
                                                <div v-else class="text-dark">
                                                    <div class="font-weight-bold">@{{ t.tienda_stock_posterior }}</div>
                                                    <div class="small text-muted" style="font-size: 0.7rem;">(Retornó @{{ t.cantidad }})</div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div v-if="!t.confirmado">
                                                    <div class="font-weight-bold text-success">@{{ t.almacen_stock_anterior }} + @{{ t.cantidad }}</div>
                                                    <div class="small font-weight-bold text-muted stock-diff-anim">= @{{ t.almacen_stock_anterior + t.cantidad }}</div>
                                                </div>
                                                <div v-else class="text-primary">
                                                    <div class="font-weight-bold">@{{ t.almacen_stock_posterior }}</div>
                                                    <div class="small text-muted" style="font-size: 0.7rem;">(Ingresó @{{ t.cantidad }})</div>
                                                </div>
                                            </td>
                                            <td class="text-right align-middle pr-3">
                                                <button v-if="!t.confirmado" @click="quitarTraslado(index)" class="btn btn-outline-danger btn-sm" v-b-tooltip.hover title="Quitar borrador">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <span v-else class="text-success small font-weight-bold"><i class="fas fa-check-circle"></i> Confirmado</span>
                                            </td>
                                        </tr>
                                        <tr v-if="trasladosFiltrados.length === 0">
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="fas fa-info-circle mr-1"></i> No hay traslados para mostrar con los filtros seleccionados
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        new Vue({
            el: '#traslados-tiendas-app',
            data() {
                return {
                    tiendas: [],
                    productos: [], // Productos de la tienda seleccionada
                    cargando: true,
                    cargandoProductos: false,
                    
                    form: {
                        tiendaId: '',
                        productoId: '',
                        cantidad: 1
                    },
                    
                    busquedaSelector: '',
                    dropdownAbierto: false,
                    
                    trasladosEnCurso: [], // Lista maestra (mezcla local + BD)
                    
                    almacenId: null,
                    userId: {{ auth()->id() ?? 'null' }},

                    // Filtros
                    filtroTienda: '',
                    filtroProducto: '',
                    filtroFecha: (() => {
                        const d = new Date();
                        const year = d.getFullYear();
                        const month = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                    })()
                }
            },
            computed: {
                productoSeleccionadoDetalle() {
                    if (!this.form.productoId) return null;
                    return this.productos.find(p => p.id === this.form.productoId);
                },
                listaSugerencias() {
                    const search = this.busquedaSelector.toLowerCase().trim();
                    if (!search || !this.dropdownAbierto) return [];
                    
                    return this.productos.filter(p => {
                        return p.nombre.toLowerCase().includes(search) || 
                               (p.alias && p.alias.toLowerCase().includes(search)) ||
                               (p.codigo && p.codigo.toLowerCase().includes(search));
                    }).slice(0, 15);
                },
                hayTrasladosPendientes() {
                    return this.trasladosEnCurso.some(t => !t.confirmado);
                },
                trasladosFiltrados() {
                    return this.trasladosEnCurso.filter(t => {
                        // Filtro por Tienda
                        const matchesTienda = !this.filtroTienda || t.tienda_id == this.filtroTienda;
                        
                        // Filtro por Producto (Nombre o Código)
                        const search = this.filtroProducto.toLowerCase().trim();
                        const nombre = (t.producto_nombre || t.nombre || '').toLowerCase();
                        const codigo = (t.producto_codigo || t.codigo || '').toLowerCase();
                        const matchesProducto = !search || nombre.includes(search) || codigo.includes(search);
                        
                        return matchesTienda && matchesProducto;
                    });
                }
            },
            watch: {
                filtroFecha() {
                    this.fetchDataInitial();
                },
                trasladosEnCurso: {
                    deep: true,
                    handler(newVal) {
                        // Solo persistimos localmente aquellos que NO están confirmados (borradores)
                        const pendientes = newVal.filter(t => !t.confirmado);
                        localStorage.setItem('traslados_tiendas_pendientes', JSON.stringify(pendientes));
                    }
                }
            },
            mounted() {
                // Cargar datos persistidos si existen
                const saved = localStorage.getItem('traslados_tiendas_pendientes');
                if (saved) {
                    try {
                        this.trasladosEnCurso = JSON.parse(saved);
                    } catch (e) {
                        localStorage.removeItem('traslados_tiendas_pendientes');
                    }
                }
                this.fetchDataInitial();
                
                // Lector de barras
                let buffer = '';
                let lastKeyTime = Date.now();
                document.addEventListener('keypress', (e) => {
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
                    
                    const currentTime = Date.now();
                    if (currentTime - lastKeyTime > 200) buffer = '';
                    lastKeyTime = currentTime;

                    if (e.key === 'Enter') {
                        if (buffer.length > 3) {
                            this.procesarEscaneo(buffer);
                            buffer = '';
                            e.preventDefault();
                        }
                    } else if (e.key.length === 1) {
                        buffer += e.key;
                    }
                });
            },
            methods: {
                async fetchDataInitial() {
                    try {
                        this.cargando = true;
                        const response = await axios.get('/api/inventario/traslados-tiendas/datos-gestion', {
                            params: { fecha: this.filtroFecha }
                        });
                        
                        this.tiendas = response.data.tiendas;
                        this.almacenId = response.data.almacen_id;
                        
                        // --- FUSIÓN DE DATOS (BD + LOCAL) ---
                        const confirmados = (response.data.confirmados || []).map(c => ({ ...c, confirmado: true }));
                        
                        const hoyStr = (() => {
                            const d = new Date();
                            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                        })();

                        const pendientesLocal = (this.filtroFecha === hoyStr) 
                            ? this.trasladosEnCurso.filter(t => !t.confirmado)
                            : [];
                        
                        // Evitamos duplicidad si un (PRODUCTO, TIENDA) ya se guardó en BD
                        const keysConfirmados = confirmados.map(c => `${c.id}-${c.tienda_id}`);
                        const borradoresSinDuplicados = pendientesLocal.filter(p => !keysConfirmados.includes(`${p.producto_id}-${p.tienda_id}`));

                        // Unimos: Primero borradores (nuevos), luego confirmados
                        this.trasladosEnCurso = [...borradoresSinDuplicados, ...confirmados];
                        
                    } catch (error) {
                        console.error('Error al cargar datos:', error);
                    } finally {
                        this.cargando = false;
                    }
                },
                seleccionarTienda(id) {
                    if (this.form.tiendaId === id) return;
                    
                    this.form.tiendaId = id;
                    this.form.productoId = '';
                    this.busquedaSelector = '';
                    this.productos = [];
                    this.fetchProductosTienda(id);
                },
                fetchProductosTienda(id) {
                    this.cargandoProductos = true;
                    axios.get(`/api/inventario/traslados-tiendas/productos/${id}`)
                        .then(res => {
                            this.productos = res.data;
                            this.cargandoProductos = false;
                        });
                },
                seleccionarProducto(prod) {
                    this.form.productoId = prod.id;
                    this.form.cantidad = 1;
                    this.busquedaSelector = prod.alias || prod.nombre;
                    this.dropdownAbierto = false;
                    this.agregarAListaPrevia();
                },
                limpiarSeleccionProducto() {
                    this.form.productoId = '';
                    this.form.cantidad = 1;
                    this.busquedaSelector = '';
                },
                cerrarDropdownDespues() {
                    setTimeout(() => { this.dropdownAbierto = false; }, 200);
                },
                cambiarCantidad(delta) {
                    if (!this.productoSeleccionadoDetalle) return;
                    
                    let nueva = this.form.cantidad + delta;
                    if (nueva < 1) nueva = 1;
                    if (nueva > this.productoSeleccionadoDetalle.stock_tienda) {
                        nueva = this.productoSeleccionadoDetalle.stock_tienda;
                    }
                    this.form.cantidad = nueva;
                    this.actualizarCantidadEnLista();
                },
                agregarAListaPrevia() {
                    if (!this.form.tiendaId || !this.form.productoId) return;
                    
                    const tienda = this.tiendas.find(t => t.id === this.form.tiendaId);
                    const prod = this.productoSeleccionadoDetalle;
                    
                    // Si ya existe en la lista MAESTRA como borrador, incrementamos
                    const index = this.trasladosEnCurso.findIndex(t => !t.confirmado && t.producto_id === prod.id && t.tienda_id === tienda.id);
                    
                    if (index !== -1) {
                        const t = this.trasladosEnCurso[index];
                        if (t.cantidad < prod.stock_tienda) {
                            t.cantidad++;
                            this.form.cantidad = t.cantidad;
                        }
                        return;
                    }

                    const now = new Date();
                    const fechaVisual = this.formatearFecha(now);

                    this.trasladosEnCurso.unshift({
                        confirmado: false,
                        tienda_id: tienda.id,
                        tienda_nombre: tienda.nombre,
                        tienda_alias: tienda.alias,
                        producto_id: prod.id,
                        producto_nombre: prod.nombre,
                        producto_alias: prod.alias,
                        producto_codigo: prod.codigo,
                        cantidad: 1,
                        tienda_stock_anterior: prod.stock_tienda,
                        almacen_stock_anterior: prod.stock_almacen,
                        fecha_visual: fechaVisual
                    });
                },
                actualizarCantidadEnLista() {
                    const index = this.trasladosEnCurso.findIndex(t => !t.confirmado && t.producto_id === this.form.productoId && t.tienda_id === this.form.tiendaId);
                    if (index !== -1) {
                        this.trasladosEnCurso[index].cantidad = this.form.cantidad;
                    }
                },
                quitarTraslado(index) {
                    const item = this.trasladosFiltrados[index];
                    this.trasladosEnCurso = this.trasladosEnCurso.filter(t => t !== item);
                    if (this.form.productoId === item.producto_id) {
                        this.limpiarSeleccionProducto();
                    }
                },
                procesarEscaneo(code) {
                    if (!this.form.tiendaId) {
                        Swal.fire('Atención', 'Primero seleccione una tienda para buscar sus productos', 'warning');
                        return;
                    }
                    
                    const prod = this.productos.find(p => p.codigo === code);
                    if (prod) {
                        this.seleccionarProducto(prod);
                    } else {
                        Swal.fire('No encontrado', 'El producto no existe en esta tienda o no tiene stock', 'error');
                    }
                },
                procesarTraslados() {
                    const pendientes = this.trasladosEnCurso.filter(t => !t.confirmado);
                    if (pendientes.length === 0) return;

                    this.cargando = true;
                    
                    const payload = {
                        traslados: pendientes.map(t => ({
                            tienda_id: t.tienda_id,
                            producto_id: t.producto_id,
                            cantidad: t.cantidad,
                            tienda_stock_anterior: t.tienda_stock_anterior,
                            almacen_stock_anterior: t.almacen_stock_anterior,
                            tienda_stock_posterior: t.tienda_stock_anterior - t.cantidad,
                            almacen_stock_posterior: t.almacen_stock_anterior + t.cantidad
                        })),
                        user_id: this.userId
                    };

                    axios.post('/api/inventario/traslados-tiendas/guardar', payload)
                        .then(res => {
                            Swal.fire('Éxito', res.data.message, 'success').then(() => {
                                // Limpiamos solo los borradores guardados
                                this.trasladosEnCurso = this.trasladosEnCurso.filter(t => t.confirmado);
                                localStorage.removeItem('traslados_tiendas_pendientes');
                                
                                const tiendaActual = this.form.tiendaId;
                                this.limpiarSeleccionProducto();
                                this.fetchDataInitial(); // Recargar datos maestros y confirmados
                                
                                // Refrescar stock de productos de la tienda actual para el buscador
                                if (tiendaActual) {
                                    this.fetchProductosTienda(tiendaActual);
                                }
                            });
                        })
                        .catch(err => {
                            this.cargando = false;
                            Swal.fire('Error', err.response.data.message || 'Error al procesar', 'error');
                        });
                },
                formatearFecha(fechaStr) {
                    if (!fechaStr) return '---';
                    const d = new Date(fechaStr);
                    if (isNaN(d.getTime())) return fechaStr; 
                    
                    const dia = String(d.getDate()).padStart(2, '0');
                    const mes = String(d.getMonth() + 1).padStart(2, '0');
                    const anio = d.getFullYear();
                    
                    let horas = d.getHours();
                    const ampm = horas >= 12 ? 'PM' : 'AM';
                    horas = horas % 12;
                    horas = horas ? horas : 12; 
                    const horasStr = String(horas).padStart(2, '0');
                    const min = String(d.getMinutes()).padStart(2, '0');
                    
                    return `${dia}/${mes}/${anio} ${horasStr}:${min} ${ampm}`;
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
