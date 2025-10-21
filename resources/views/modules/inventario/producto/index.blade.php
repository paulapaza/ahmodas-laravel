<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Productos</x-slot>

    <x-table>
        <th>id</th>
        <th>Barcode</th>
        <th class="col-nombre-producto">Nombre</th>
        <th>alias</th>
        <th>stock</th>
        <th>costo</th>
        <th>precio</th>
        <th>precio minimo</th>
        <th>precio x mayor</th>
        <th>categoria</th>
        <th>estado</th>
        <th>acciones</th>
    </x-table>

    <x-mymodal size="modal-lg">

        @csrf
        <input type="hidden" id="id" name="id">
        <div class="row ">
            <div class="form-group col-12">
                <label for="nombre" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" autocomplete="off" required>
            </div>
            <div class="form-group col-12">
                <label for="alias" class="form-label">Alias</label>
                <input type="text" class="form-control" id="alias" name="alias" autocomplete="off">
            </div>

            <div class="form-group col-3">
                <label for="costo_unitario" class="form-label text-xaccent">Precio Compra </label>
                <input type="number" class="form-control" id="costo_unitario" step="0.01" name="costo_unitario"
                    autocomplete="off" required>
            </div>
            <div class="form-group col-3">
                <label for="precio_unitario" class="form-label text-xaccent">Precio Venta </label>
                <input type="number" class="form-control" id="precio_unitario" name="precio_unitario"
                    autocomplete="off" required step="0.01">
            </div>

            <div class="form-group col-3">
                <label for="precio_minimo" class="form-label">Precio Venta min</label>
                <input type="number" class="form-control" id="precio_minimo" name="precio_minimo" autocomplete="off"
                    required step="0.01">
            </div>

            <div class="form-group col-3">
                <label for="precio_x_mayor" class="form-label text-success ">Precio Venta x Mayor</label>
                <input type="number" class="form-control" id="precio_x_mayor" name="precio_x_mayor" autocomplete="off"
                    step="0.01">
            </div>
            <div class="form-group col-4">
                <label for="codigo_barras" class="form-label">Código de barras</label>
                <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" autocomplete="off"
                    required>
            </div>

            <div class="form-group col-4">
                <label for="marca_id" class="form-label">Marca</label>
                <select class="form-control " id="marca_id" name="marca_id" clean="false" required>

                </select>
            </div>
            <div class="form-group col-4">
                <label for="categoria_id" class="form-label">Categoria</label>
                <select class="form-control" id="categoria_id" name="categoria_id" clean="false" required>

                </select>
            </div>

            <div class="form-group col-4">
                <label for="moneda" class="form-label">Tipo de Moneda</label>
                <select class="form-control" id="moneda" name="moneda" required>
                    <option value="1" selected>PEN</option>
                    <option value="2">USD</option>
                </select>
            </div>

            <div class="form-group col-8">
                <label for="tipo_de_igv" class="form-label">Tipo de IGV</label>
                <select class="form-control" id="tipo_de_igv" name="tipo_de_igv" required>

                    <option value="1" selected>Gravado - Operación Onerosa</option>
                    <option value="8">Exonerado - Operación Onerosa</option>
                    <option value="9">Inafecto - Operación Onerosa</option>
                    <option value="17">Exonerado - Transferencia Gratuita</option>
                    <option value="20">Inafecto - Transferencia Gratuita</option>
                    <option value="16">Exportación</option>
                    {{-- <option value="2">Gravado - Retiro por premio</option>
                    <option value="3">Gravado - Retiro por donación</option>
                    <option value="4">Gravado - Retiro</option>
                    <option value="5">Gravado - Retiro por publicidad</option>
                    <option value="6">Gravado - Bonificaciones</option>
                    <option value="7">Gravado - Retiro por entrega a trabajadores</option>
                    <option value="10">Inafecto - Retiro por Bonificación</option>
                    <option value="11">Inafecto - Retiro</option>
                    <option value="12">Inafecto - Retiro por Muestras Médicas</option>
                    <option value="13">Inafecto - Retiro por Convenio Colectivo</option>
                    <option value="14">Inafecto - Retiro por premio</option>
                    <option value="15">Inafecto - Retiro por publicidad</option> --}}

                </select>
            </div>
        </div>
        <h5 class="text-danger">Stock por tienda</h5>
        <div id="stocks_por_tienda" class="row"></div>


    </x-mymodal>

    <div id="productos-index">
        <div
            class="modal fade"
            id="modal-historial-salidas"
            tabindex="-1"
            role="dialog"
            aria-labelledby="tituloHistorial"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <!-- HEADER -->
                <div class="modal-header bg-xprimary text-white">
                    <h5 style="margin-bottom: 0; line-height: 1.5;" id="tituloHistorial">
                        Historial de stock
                    </h5>
                    <button
                        type="button"
                        class="close text-white"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center align-middle">Tienda</th>
                            <th class="text-center align-middle">Stock anterior</th>
                            <th class="text-center align-middle">Cantidad reducida</th>
                            <th class="text-center align-middle">Stock resultante</th>
                            <th class="text-center align-middle">Tipo</th>
                            <th class="text-center align-middle">Comentario</th>
                            <th class="text-center align-middle">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <div v-if="producto">
                            <h5><strong>Código de barras:</strong> @{{producto.codigo_barras }}</h5>
                            <h5><strong>Nombre:</strong> @{{producto.nombre }}</h5>
                        </div>
                        <tr v-for="(item, index) in historial" :key="item.id">
                            <td class="text-center align-middle">@{{ item.tienda_nombre }}</td>
                            <td class="text-center align-middle">@{{ item.stock_antes }}</td>
                            <td class="text-center align-middle text-danger font-weight-bold">@{{ item.cantidad_reducida }}</td>
                            <td class="text-center align-middle text-success font-weight-bold">@{{ item.stock_despues }}</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-danger" v-if="item.tipo === 1">Manual</span>
                                <span class="badge badge-success" v-else-if="item.tipo === 2">Venta</span>
                                <span v-else>-</span>
                            </td>
                            <td class="text-center align-middle">@{{ item.comentario }}</td>
                            <td class="text-center align-middle">@{{ formatFecha(item.created_at) }}</td>
                        </tr>

                        <tr v-if="historial.length === 0">
                            <td colspan="6" class="text-center text-muted py-4">
                            No hay registros de reducciones para este producto.
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>

</x-admin-layout>
<script>
    $(document).ready(function() {


        let csrf = $('input[name="_token"]').val();
        // Inicializamos las variables para tipo de envio por ajax en Store record
        let dataCrud = {
            route: "/inventario/producto",
            subject: 'producto',
            model: "Producto",
            csrf: csrf,
        };
        let table = new Larajax({
            data: dataCrud,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'codigo_barras',
                },
                {
                    data: 'nombre',
                    className: 'col-nombre-producto'

                },
                {
                    data: 'alias'
                },
                {
                    data: 'total_stock',
                    render: function (data, type, row) {
                        if (data === 0 || data === "0") {
                            return data;
                        } else {
                            return `<span class="edit-btn text-primary" style="cursor: pointer">${data}</span>`;
                        }
                    },
                },
                {
                    data: 'costo_unitario'
                },
                {
                    data: 'precio_unitario'
                },

                {
                    data: 'precio_minimo'
                },
                {
                    data: 'precio_x_mayor',
                    visible: window.currentUserPermissions?.includes('ver-precio-x-mayor') //
                },
                {
                    data: 'categoria_id',
                    render: function (data, type, row) {
                        return row.categoria_nombre;
                    }
                },
                {
                    data: 'estado',
                    render: function(data, type, row) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' :
                            '<span class="badge bg-xsecondary text-white">Inactivo</span>'
                    }
                },

            ],
            actionsButtons: {
                edit: true,
                destroy: true
            },
            //alingCenter: [7]

        })

        cargarStocks(); // sin parámetro
        // edit record

        /*  $('#table').on('click', '.btn-edit', function() {
             let rowData = ($(this).parents('tr').hasClass('child')) ?
                 table.row($(this).parents().prev('tr')).data() :
                 table.row($(this).parents('tr')).data();
             await cargarStocks(rowData.id);

             edit_record(rowData, table, $(this));
         
         }); */
        $('#table').on('click', '.btn-edit', async function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

            await cargarStocks(rowData.id); // ✅ ahora sí puedes usar await aquí

            edit_record(rowData, table, $(this));
        });

        // store record
        $(document).on('click', '.btn-store', function() {

            no_send_form('#form');

            let form = document.getElementById('form');

            if (!form.checkValidity()) {
                return;
            }
            let formData = new FormData(form);
            // console.log(formData);

            store_record(dataCrud, formData, table);

        });

        // destroy record
        $('#table').on("click", ".btn-destroy", function() {
            console.log('destroy')

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

            destroy_record(dataCrud, table, rowData)
        });

        //cargar select de categorias
        $.ajax({
            url: '/inventario/categoria',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                data.forEach(element => {
                    if (element.id == 1) {
                        html +=
                            `<option value="${element.id}" selected>${element.nombre}</option>`;
                    } else {
                        html += `<option value="${element.id}">${element.nombre}</option>`;
                    }
                });
                $('#categoria_id').html(html);
            }
        });
        //cargar select de marcas
        $.ajax({
            url: '/inventario/marca',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                data.forEach(element => {
                    if (element.id == 1) {
                        html +=
                            `<option value="${element.id}" selected>${element.nombre}</option>`;
                    } else {
                        html += `<option value="${element.id}">${element.nombre}</option>`;
                    }
                });
                $('#marca_id').html(html);
            }
        });
        //stock por tiendas
        // Llama esto al cargar la vista o al abrir el modal de crear/editar
        /* function async cargarStocks(productoId = null) {
            $.ajax({
                url: '/inventario/stock',
                data: {
                    producto_id: productoId
                },
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    data.forEach(tienda => {
                        html += `
                    <div class="form-group mb-2">
                        <label for="stock_tienda_${tienda.id}">${tienda.nombre}</label>
                        <input type="number"
                            name="stocks[${tienda.id}]"
                            id="stock_tienda_${tienda.id}"
                            class="form-control"
                            value="${tienda.stock}">
                    </div>`;
                    });
                    $('#stocks_por_tienda').html(html);
                }
            });
        }
 */
        async function cargarStocks(productoId = null) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/inventario/stock',
                    data: {
                        producto_id: productoId
                    },
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let html = '';
                        data.forEach(tienda => {

                            html += `
                        <div class="form-group mb-2 col-3">
                            <label for="stock_tienda_${tienda.id}">${tienda.nombre}</label>
                            <input type="number"
                                name="stocks[${tienda.id}]"
                                id="stock_tienda_${tienda.id}"
                                class="form-control"
                                value="${tienda.stock}">
                        </div>`;
                        });
                        $('#stocks_por_tienda').html(html);
                        resolve(); // <- resuelve la promesa después de completar
                    },
                    error: function(xhr, status, error) {
                        reject(error); // <- en caso de error
                    }
                });
            });
        }

        // guardamos la intacia de larajax
        window.productosIndexTable = table;
    });

    new Vue({
        el: "#productos-index",
        data() {
            return {
                productoIdHistorial: null,
                producto: null,
                historial: [],
                camposHistorial: [
                    { key: "index", label: "#" },
                    { key: "tienda_nombre", label: "Tienda" },
                    { key: "stock_antes", label: "Stock Anterior" },
                    { key: "cantidad_reducida", label: "Se Redujo" },
                    { key: "stock_despues", label: "Stock Actual" },
                    { key: "created_at", label: "Fecha de Reducción" },
                ],
            }
        },
        mounted() {
            const vm = this;
            $('#table').on('click', '.edit-btn', function() {
                const rowData = window.productosIndexTable.row($(this).closest('tr')).data();
                vm.abrirHistorial(rowData);
            });
        },
        methods: {
            abrirHistorial(producto) {
                this.productoIdHistorial = producto.id;
                this.producto = producto;
                this.cargarHistorial();
                $("#modal-historial-salidas").modal("show");
            },
            async cargarHistorial() {
                if (!this.productoIdHistorial) return;
                try {
                    const { data } = await window.api.get(`/inventario/salidas/historial/${this.productoIdHistorial}`);
                    this.historial = data;
                } catch (error) {
                    console.error("❌ Error al cargar historial:", error);
                    this.historial = [];
                    this.$bvToast.toast("No se pudo cargar el historial.", {
                        title: "Error",
                        variant: "danger",
                        solid: true,
                    });
                }
            },
            formatFecha(fecha) {
                if (!fecha) return "-";
                const f = new Date(fecha);
                return f.toLocaleString("es-ES", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                });
            },
        },
    });
</script>
