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
        
        .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important; }
        .table-sm-text { font-size: 0.85rem; }
        
        [v-cloak] { display: none; }

        .filter-bar {
            background: rgba(248, 249, 250, 0.85);
            backdrop-filter: blur(5px);
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #e3e6f0;
        }
    </style>
    @endpush

    <div id="historial-traslados-app" v-cloak>
        <div class="container-fluid py-4">

            <!-- Header con Botón de Regreso -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 font-weight-bold text-dark">
                        <i class="fas fa-history mr-2 text-primary"></i> HISTORIAL DE TRASLADOS
                    </h4>
                    @unless(auth()->user()->hasRole('cajero'))
                    <a href="{{ route('inventario.traslados_almacen.index') }}" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Gestión
                    </a>
                    @endunless
                </div>
            </div>

            <!-- Filtros Avanzados -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="filter-bar">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted uppercase">Tienda Destino:</label>
                                <select class="form-control form-control-sm shadow-none" v-model="filtros.tienda_id" @change="fetchHistorial">
                                    <option value="">Todas las tiendas</option>
                                    <option v-for="t in tiendas" :key="t.id" :value="t.id">@{{ t.nombre }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted uppercase">Filtrar por Fecha:</label>
                                <input type="date" class="form-control form-control-sm shadow-none" v-model="filtros.fecha" @change="fetchHistorial">
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-muted uppercase">Producto:</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm border-left-0 shadow-none" 
                                           placeholder="Nombre o código..." 
                                           v-model="filtros.search" 
                                           @input="debounceFetch">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button @click="resetFiltros" class="btn btn-outline-secondary btn-sm btn-block">
                                    <i class="fas fa-sync-alt mr-1"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Resultados -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm mb-0 table-sm-text">
                            <thead class="bg-light text-muted uppercase">
                                <tr>
                                    <th class="pl-4 py-3 border-top-0">Producto</th>
                                    <th class="py-3 border-top-0">Tienda</th>
                                    <th class="text-center py-3 border-top-0">Vendido</th>
                                    <th class="text-center py-3 border-top-0">Disponible</th>
                                    <th class="text-center py-3 border-top-0">En Almacén</th>
                                    <th class="py-3 border-top-0">Fecha Registro</th>
                                    <th class="py-3 border-top-0 pr-4">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="cargando">
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                                        Cargando historial...
                                    </td>
                                </tr>
                                <tr v-else v-for="t in traslados" :key="t.traslado_id">
                                    <td class="pl-4 align-middle">
                                        <div class="font-weight-bold text-dark">@{{ t.alias || t.nombre }}</div>
                                        <div class="small text-muted font-weight-bold">Código: @{{ t.codigo || 'S/C' }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border px-3 py-2 font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            @{{ t.tienda_nombre }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">@{{ t.vendido }}</td>
                                    <td class="text-center align-middle text-primary font-weight-bold">@{{ t.disponible }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-light border text-muted px-3 py-2" style="font-size: 0.85rem;">
                                            @{{ t.stock_almacen }} @{{ t.stock_almacen === 1 ? 'unidad' : 'unidades' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">@{{ t.created_fmt.split(' ')[0] }}</div>
                                        <div class="small text-muted">@{{ t.created_fmt.split(' ')[1] }}</div>
                                    </td>
                                    <td class="align-middle pr-4">
                                        <div class="font-weight-bold text-dark">@{{ t.user_name || 'No disponible' }}</div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">@{{ t.user_email || '-' }}</div>
                                    </td>
                                </tr>
                                <tr v-if="!cargando && traslados.length === 0">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> No se encontraron registros con los filtros aplicados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="small text-muted">
                        Se muestran <strong>@{{ traslados.length }}</strong> registros históricos encontrados.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        new Vue({
            el: '#historial-traslados-app',
            data() {
                return {
                    tiendas: [],
                    traslados: [],
                    filtros: {
                        tienda_id: '',
                        fecha: '{{ date('Y-m-d') }}',
                        search: ''
                    },
                    cargando: false,
                    timeout: null
                }
            },
            mounted() {
                this.fetchHistorial();
            },
            methods: {
                async fetchHistorial() {
                    this.cargando = true;
                    try {
                        const response = await axios.get('/api/inventario/traslados/historial-global', {
                            params: {
                                tienda_id: this.filtros.tienda_id,
                                fecha: this.filtros.fecha,
                                search: this.filtros.search
                            }
                        });
                        if (response.data.success) {
                            this.traslados = response.data.traslados;
                            this.tiendas = response.data.tiendas;
                        }
                    } catch (error) {
                        console.error("Error al cargar historial", error);
                        alert("Error al conectar con el servidor.");
                    } finally {
                        this.cargando = false;
                    }
                },
                debounceFetch() {
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(() => {
                        this.fetchHistorial();
                    }, 500);
                },
                resetFiltros() {
                    this.filtros = {
                        tienda_id: '',
                        fecha: '',
                        search: ''
                    };
                    this.fetchHistorial();
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
