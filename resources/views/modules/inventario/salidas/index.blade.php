<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Stock de Productos</x-slot>

    <div id="salidas-index">
        <table id="salidas-productos-table"
            class="display responsive nowrap bordered shadow dataTable dtr-inline collapsed" style="width:100%">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Código de barras</th>
                    <th>Nombre</th>
                    <th>Alias</th>
                    <th>Stock por tienda</th>
                </tr>
            </thead>
        </table>

         <b-modal
            id="modal-reducir-stock"
            :title="title"
            ok-title="Guardar"
            @ok.prevent="guardarCambios"
            size="lg"
            header-class="bg-xprimary text-white"
            dialog-class="modal-lg"
        >
            <div>
                <h5><strong v-text="producto.codigo_barras.label"></strong> <span v-text="producto.codigo_barras.value"></span></h5>
                <h5><strong v-text="producto.nombre.label"></strong> <span v-text="producto.nombre.value"></span></h5>
            </div>

            <b-table
                :items="producto.tiendas"
                :fields="campos"
                bordered
                small
                responsive
                class="mt-3"
            >
                <!-- Nombre de la tienda -->
                <template #cell(nombre)="data">
                    @{{data.item.nombre }}
                </template>

                <!-- Stock actual -->
                <template #cell(stock_actual)="data">
                    @{{data.item.stock }}
                </template>

                <!-- Variación -->
                <template #cell(variacion)="data">
                    <b-form-input
                        type="text"
                        v-model="data.item.variacion"
                        @input="soloNumerosPositivos(data.item)"
                        @focus="$event.target.select()"
                        size="sm"
                        style="display: inline-block; width:70px; text-align:right;"
                        placeholder="0"
                    />
                </template>

                <!-- Stock resultante -->
                <template #cell(stock_resultante)="data">
                    @{{data.item.stock_resultante }}
                </template>

                <!-- Comentario -->
                <template #cell(comentario)="data">
                    <b-form-input
                        type="text"
                        v-model="data.item.comentario"
                        placeholder="Escribe un comentario..."
                        size="sm"
                    />
                </template>
            </b-table>
        </b-modal>
    </div>

</x-admin-layout>

<script>
    // Instancia principal de Vue
    Vue.use(BootstrapVue);

    new Vue({
        el: '#salidas-index',
        data() {
            return {
                producto: {
                    id: null,
                    codigo_barras: {
                        label: '',
                        value: '',
                    },
                    nombre: {
                        label: '',
                        value: '',
                    },
                    tiendas: [],
                },
                table: null,
                variacion: 'REDUCIR', // 'AUMENTAR' o 'REDUCIR'
            }
        },
        computed: {
            title() {
                return this.variacion === 'AUMENTAR'
                    ? 'Aumentar Stock de Producto'
                    : 'Reducir Stock de Producto';
            },
            campos() {
                return [
                    { key: "nombre", label: "Tienda", thClass: 'text-center align-middle', tdClass: 'text-center align-middle' },
                    { key: "stock_actual", label: "Stock Actual", thClass: 'text-center align-middle', tdClass: 'text-center align-middle' },
                    {
                        key: 'variacion',
                        label: this.variacion === 'AUMENTAR' ? 'Aumentar' : 'Reducir',
                        thClass: `${this.variacion === 'AUMENTAR' ? 'bg-success' : 'bg-danger'} text-white text-center align-middle`,
                        tdClass: 'text-center align-middle'
                    },
                    { key: "stock_resultante", label: "Stock Resultante", thClass: 'text-center align-middle', tdClass: 'text-center align-middle' },
                    { key: "comentario", label: "Comentario", thClass: 'text-center align-middle', tdClass: 'text-center align-middle' },
                ]
            },
        },
        mounted() {
            const table = initDataTable('#salidas-productos-table', {
                ajax: '{{ route('inventario.salidas.listado') }}',
                order: [[0, 'desc']],
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'codigo_barras',
                        name: 'codigo_barras'
                    },
                    {
                        data: 'nombre',
                        name: 'nombre'
                    },
                    {
                        data: 'alias',
                        name: 'alias'
                    },
                    {
                        data: 'tiendas',
                        name: 'tiendas',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return data.map(t => `<strong>${t.nombre}:</strong> ${t.stock}`)
                                .join('<br>');
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        title: 'Acciones',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-sm bg-danger text-white edit-btn">Reducir</button>
                                <button class="btn btn-sm bg-success text-white aumentar-btn">Aumentar</button>
                            `;
                        },
                    }
                ],
                layout: {
                    topStart: {
                        buttons: [
                            "pageLength",
                            "copy",
                            "excel",
                            "print",
                            "colvis"
                        ]
                    }
                }
            }, this);

            this.table = table;

            $('#salidas-productos-table').on('click', '.edit-btn', function(e) {
                const rowData = table.row($(this).closest('tr')).data();
                table.vue.variacion = 'REDUCIR';
                table.vue.editProduct(rowData);
            });

            $('#salidas-productos-table').on('click', '.aumentar-btn', function(e) {
                const rowData = table.row($(this).closest('tr')).data();
                table.vue.variacion = 'AUMENTAR';
                table.vue.editProduct(rowData);
            });
        },
        methods: {
            editProduct(row) {
                this.producto = {
                    id: row.id,
                    codigo_barras: {
                        label: 'Código de barras',
                        value: row.codigo_barras,
                    },
                    nombre: {
                        label: 'Nombre',
                        value: row.nombre,
                    },
                    tiendas: row.tiendas,
                };
                this.producto.tiendas.forEach((t) => {
                    this.$set(t, "variacion", 0);
                    this.$set(t, "stock_resultante", t.stock);
                    this.$set(t, "comentario", "");
                });
                this.abrirModal();
            },
            abrirModal() {
                this.$bvModal.show("modal-reducir-stock");
            },
            soloNumerosPositivos(tienda) {
                // Aseguramos que el valor sea string
                let valor = tienda.variacion != null ? tienda.variacion.toString() : "";

                // Filtrar solo dígitos (0-9)
                const soloNumeros = valor.replace(/[^0-9]/g, "");

                // Si no hay números válidos, limpiar el campo y mantener stock igual
                if (soloNumeros === "") {
                    tienda.variacion = "";
                    tienda.stock_resultante = tienda.stock; // sin cambios
                    return;
                }

                // Convertir a número
                let numero = parseInt(soloNumeros, 10);

                // Si no es número válido o negativo, poner 0
                if (isNaN(numero) || numero < 0) {
                    numero = 0;
                }

                // Calcular stock resultante (puede ser negativo si excede)
                tienda.stock_resultante = this.variacion === 'AUMENTAR'
                    ? tienda.stock + numero
                    : tienda.stock - numero;

                // Asignar valor limpio
                tienda.variacion = numero;
            },

            guardarCambios() {
                const datos = this.producto.tiendas
                    .filter(t => t.stock_resultante !== t.stock)
                    .map((t) => ({
                        producto_id: this.producto.id,
                        tienda_id: t.id,
                        stock_antes: t.stock,
                        cantidad_reducida: t.variacion,
                        stock_despues: t.stock_resultante,
                        tipo: this.variacion === 'REDUCIR' ? 1 : 3,
                        comentario: t.comentario,
                    }));
                console.log('Datos a enviar:', datos);
                if (datos.length === 0) return;

                window.api.post('/inventario/salidas/reducir', datos)
                    .then(res => {
                        console.log('✅ Respuesta del servidor:', res);
                        this.$bvToast.toast('Stock reducido correctamente.', {
                            title: 'Éxito',
                            variant: 'success',
                            solid: true
                        });

                        this.$bvModal.hide('modal-reducir-stock');

                        if (this.table) {
                            this.table.ajax.reload(null, false);
                        }
                    })
                    .catch(err => {
                        console.error('❌ Error al guardar:', err);
                        this.$bvToast.toast('Hubo un error al guardar los cambios.', {
                            title: 'Error',
                            variant: 'danger',
                            solid: true
                    });
                });
            },
        },
    })
</script>
