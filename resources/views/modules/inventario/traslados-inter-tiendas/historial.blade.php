<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Historial de Traslados Entre Tiendas</x-slot>

    @push('styles')
    <style>
        :root {
            --xprimary: #858796;
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

        /* Estilo para el nombre de la tienda */
        .store-badge {
            font-size: 0.95rem;
            padding: 5px 12px;
            font-weight: 700;
        }
    </style>
    @endpush

    <div id="historial-inter-tiendas-app" v-cloak>
        <div class="container-fluid py-4">

            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 font-weight-bold text-dark">
                        <i class="fas fa-history mr-2 text-secondary"></i> HISTORIAL: ENTRE TIENDAS
                    </h4>
                    <a href="{{ route('inventario.traslados_inter_tiendas.index') }}" class="btn btn-secondary shadow-sm px-4">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Gestión
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="filter-bar">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted uppercase">Origen:</label>
                                <select class="form-control form-control-sm shadow-none" v-model="filtros.tienda_origen_id" @change="fetchHistorial">
                                    <option value="">Todos</option>
                                    <option v-for="t in tiendas" :key="t.id" :value="t.id">@{{ t.nombre }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted uppercase">Destino:</label>
                                <select class="form-control form-control-sm shadow-none" v-model="filtros.tienda_destino_id" @change="fetchHistorial">
                                    <option value="">Todos</option>
                                    <option v-for="t in tiendas" :key="t.id" :value="t.id">@{{ t.nombre }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted uppercase">Fecha:</label>
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

            <!-- Tabla -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm mb-0 table-sm-text">
                            <thead class="bg-light text-muted uppercase">
                                <tr>
                                    <th class="pl-4 py-3 border-top-0">ID</th>
                                    <th class="py-3 border-top-0">Origen</th>
                                    <th class="py-3 border-top-0">Destino</th>
                                    <th class="py-3 border-top-0">Producto</th>
                                    <th class="text-center py-3 border-top-0">Cantidad</th>
                                    <th class="text-center py-3 border-top-0">Stock Origen (Ant/Desp)</th>
                                    <th class="text-center py-3 border-top-0">Stock Destino (Ant/Desp)</th>
                                    <th class="py-3 border-top-0">Fecha y Hora</th>
                                    <th class="py-3 border-top-0 pr-4">Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="cargando">
                                    <td colspan="9" class="text-center py-5">
                                        <div class="spinner-border spinner-border-sm text-secondary mr-2" role="status"></div>
                                        Cargando historial...
                                    </td>
                                </tr>
                                <tr v-else v-for="t in traslados" :key="t.traslado_id">
                                    <td class="pl-4 align-middle">@{{ t.traslado_id }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border store-badge">@{{ t.tienda_origen }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border store-badge">@{{ t.tienda_destino }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">@{{ t.producto_alias || t.producto_nombre }}</div>
                                        <div class="small text-muted font-weight-bold">@{{ t.codigo || 'S/C' }}</div>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold text-primary" style="font-size: 1.1rem;">
                                        @{{ t.cantidad }}
                                    </td>
                                    <td class="text-center align-middle" style="font-size: 0.95rem;">
                                        <span class="text-muted small">@{{ t.stock_origen_anterior }} ➔</span>
                                        <span class="font-weight-bold text-danger">@{{ t.stock_origen_posterior }}</span>
                                    </td>
                                    <td class="text-center align-middle" style="font-size: 0.95rem;">
                                        <span class="text-muted small">@{{ t.stock_destino_anterior }} ➔</span>
                                        <span class="font-weight-bold text-success">@{{ t.stock_destino_posterior }}</span>
                                    </td>
                                    <td class="align-middle" style="font-size: 0.9rem;">
                                        <div class="font-weight-bold">@{{ t.created_fmt.split(' ')[0] }}</div>
                                        <div class="small text-muted">@{{ t.created_fmt.split(' ').slice(1).join(' ') }}</div>
                                    </td>
                                    <td class="align-middle pr-4" style="font-size: 0.95rem;">
                                        <div class="font-weight-bold text-dark">@{{ t.user_name || 'Sistema' }}</div>
                                    </td>
                                </tr>
                                <tr v-if="!cargando && traslados.length === 0">
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        No se encontraron registros.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        new Vue({
            el: '#historial-inter-tiendas-app',
            data() {
                return {
                    tiendas: [],
                    traslados: [],
                    filtros: {
                        tienda_origen_id: '',
                        tienda_destino_id: '',
                        fecha: '{{ date('Y-m-d') }}',
                        search: ''
                    },
                    cargando: false,
                    timeout: null
                }
            },
            mounted() {
                this.fetchInitialData();
                this.fetchHistorial();
            },
            methods: {
                fetchInitialData() {
                    axios.get('/api/inventario/traslados-entre-tiendas/datos-gestion')
                        .then(res => { this.tiendas = res.data.tiendas; });
                },
                async fetchHistorial() {
                    this.cargando = true;
                    try {
                        const response = await axios.get('/api/inventario/traslados-entre-tiendas/historial-global', {
                            params: this.filtros
                        });
                        this.traslados = response.data.traslados;
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.cargando = false;
                    }
                },
                debounceFetch() {
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(() => { this.fetchHistorial(); }, 500);
                },
                resetFiltros() {
                    this.filtros = { tienda_origen_id: '', tienda_destino_id: '', fecha: '', search: '' };
                    this.fetchHistorial();
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
