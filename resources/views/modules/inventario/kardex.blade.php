<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Movimientos de Inventario (Kardex)</x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/vue-select@3.20.2/dist/vue-select.css">
        <style>
            .vs--searchable .vs__dropdown-toggle {
                height: 38px;
                border-radius: 0.25rem;
                border: 1px solid #ced4da;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/vue-select@3.20.2/dist/vue-select.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        {{-- DataTables Export Requirements --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    @endpush

    <div id="kardex-app" class="container-fluid">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-light">
                <div class="row align-items-end">
                    <!-- Producto -->
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Producto:</label>
                        <v-select 
                            v-model="productoSeleccionado" 
                            :options="productos" 
                            label="nombre"
                            :filterable="false"
                            @search="onSearchProduct"
                            placeholder="Todos los productos..."
                        >
                            <template #option="option">
                                <div class="d-flex justify-content-between">
                                    <span>@{{ option.nombre }}</span>
                                    <small class="text-muted">@{{ option.codigo_barras }}</small>
                                </div>
                            </template>
                        </v-select>
                    </div>

                    <!-- Rango de Fechas -->
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Desde:</label>
                        <b-form-input v-model="filtros.fecha_inicio" type="date"></b-form-input>
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold">Hasta:</label>
                        <b-form-input v-model="filtros.fecha_fin" type="date"></b-form-input>
                    </div>

                    <!-- Tienda -->
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Tienda:</label>
                        <b-form-select
                            v-model="filtros.tienda_id"
                            :options="opcionesTiendas"
                        ></b-form-select>
                    </div>

                    <!-- Botón Filtrar -->
                    <div class="col-md-2">
                        <b-button variant="primary" block @click="actualizarTabla">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </b-button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Kardex -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table id="kardex-table" class="table table-hover table-striped mb-0" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tienda</th>
                            <th>Tipo</th>
                            <th>Movimiento</th>
                            <th>Comentario</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>

<script>
    Vue.component('v-select', VueSelect.VueSelect);

    new Vue({
        el: '#kardex-app',
        data() {
            return {
                productoSeleccionado: null,
                productos: [],
                filtros: {
                    producto_id: null,
                    tienda_id: null,
                    fecha_inicio: moment().startOf('month').format('YYYY-MM-DD'),
                    fecha_fin: moment().format('YYYY-MM-DD')
                },
                tiendas: [],
                table: null
            }
        },
        watch: {
            productoSeleccionado(val) {
                this.filtros.producto_id = val ? val.id : null;
            }
        },
        computed: {
            opcionesTiendas() {
                const options = [{ value: null, text: 'Todas las Tiendas' }];
                if (Array.isArray(this.tiendas)) {
                    this.tiendas.forEach(t => {
                        options.push({ value: t.id, text: t.nombre });
                    });
                }
                return options;
            }
        },
        mounted() {
            this.cargarTiendas();
            
            // Cargar producto si viene por URL
            const urlParams = new URLSearchParams(window.location.search);
            const productoId = urlParams.get('producto_id');
            if (productoId) {
                this.cargarProductoPorId(productoId);
            }

            this.inicializarTabla();
        },
        methods: {
            cargarProductoPorId(id) {
                window.api.get(`/inventario/producto/${id}`)
                    .then(res => {
                        this.productoSeleccionado = res; // window.api ya retorna response.data
                        this.filtros.producto_id = id;
                        this.$nextTick(() => {
                            this.actualizarTabla();
                        });
                    });
            },
            onSearchProduct(search, loading) {
                if(search.length < 2) return;
                loading(true);
                window.api.post('/inventario/producto/buscar', { query: search })
                    .then(res => {
                        // window.api ya retorna response.data a través del interceptor
                        this.productos = Array.isArray(res) ? res : (res.data || []);
                        loading(false);
                    })
                    .catch(err => {
                        console.error('Error buscando productos:', err);
                        loading(false);
                    });
            },
            inicializarTabla() {
                const self = this;
                this.table = $('#kardex-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('inventario.kardex.data') }}',
                        data: function (d) {
                            d.producto_id = self.filtros.producto_id;
                            d.tienda_id = self.filtros.tienda_id;
                            d.fecha_inicio = self.filtros.fecha_inicio;
                            d.fecha_fin = self.filtros.fecha_fin;
                        }
                    },
                    columns: [
                        { data: 'created_at', render: date => moment(date).format('DD/MM/YYYY HH:mm') },
                        { data: 'producto' },
                        { data: 'tienda' },
                        { data: 'tipo' },
                        { data: 'cantidad_reducida', className: 'text-center font-weight-bold' },
                        { data: 'comentario' }
                    ],
                    order: [[0, 'desc']],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                    },
                    dom: 'Bfrtip',
                    buttons: ['copy', 'excel', 'pdf', 'print']
                });
            },
            actualizarTabla() {
                if (this.table) {
                    this.table.ajax.reload();
                }
            },
            cargarTiendas() {
                window.api.get('{{ route('inventario.salidas.tiendas.listado') }}')
                    .then(res => {
                        // window.api ya retorna response.data
                        this.tiendas = res.data || res;
                    })
                    .catch(err => {
                        console.error('Error al cargar tiendas:', err);
                    });
            },
        }
    });
</script>
