<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Productos</x-slot>

    <style>
        .buttons-print {
            display: none !important;
        }
    </style>

    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body bg-light rounded py-2">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label for="filtroTienda" class="form-label font-weight-bold text-muted small text-uppercase mb-1">
                        <i class="fas fa-store mr-1"></i> Filtrar por Tienda
                    </label>
                    <select id="filtroTienda" class="form-control form-control-sm shadow-sm border-0">
                        <option value="">Todas las Tiendas</option>
                        @foreach(\Illuminate\Support\Facades\DB::table('tiendas')->select('id', 'nombre', 'alias')->orderByRaw("CASE WHEN LOWER(TRIM(alias)) = 'tienda 2' OR nombre LIKE '%002%' OR id = 4 THEN 0 ELSE 1 END, id ASC")->get() as $t)
                            <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mt-3 mt-md-0 pt-md-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="switchOcultarSinStock">
                        <label class="custom-control-label font-weight-bold text-dark" for="switchOcultarSinStock" style="cursor: pointer;">
                            Ocultar productos sin stock
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        <th>F.Creación</th>
        <th>estado</th>
        <th>Stock por tienda</th>
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
                <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" autocomplete="off"
                    required step="0.01">
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
        <div class="modal fade" id="modal-historial-salidas" tabindex="-1" role="dialog"
            aria-labelledby="tituloHistorial" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <!-- HEADER -->
                    <div class="modal-header bg-xprimary text-white">
                        <h5 style="margin-bottom: 0; line-height: 1.5;" id="tituloHistorial">
                            Historial de stock
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">
                        <div class="d-flex justify-content-between">
                            <div v-if="producto">
                                <h6><strong>Código de barras:</strong> @{{producto.codigo_barras }}</h6>
                                <h6><strong>Nombre:</strong> @{{producto.nombre }}</h6>
                            </div>
                            <b-form-group label-for="tienda-select">
                                <b-form-select id="tienda-select" v-model="selectedTienda" :options="tiendas"
                                    value-field="id" text-field="nombre"></b-form-select>
                            </b-form-group>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center align-middle">Tienda</th>
                                        <th class="text-center align-middle">Stock anterior</th>
                                        <th class="text-center align-middle">Variación</th>
                                        <th class="text-center align-middle">Stock resultante</th>
                                        <th class="text-center align-middle">Tipo</th>
                                        <th class="text-center align-middle">Comentario</th>
                                        <th class="text-center align-middle">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in filteredHistorial" :key="item.id">
                                        <td class="text-center align-middle">@{{ item.tienda_nombre }}</td>
                                        <td class="text-center align-middle">@{{ item.stock_antes }}</td>
                                        <td class="text-center align-middle">
                                            @{{ item.cantidad_reducida }}
                                            <i class="fa-solid fa-arrow-down text-danger"
                                                v-if="[1, 2].includes(item.tipo)"></i>
                                            <i class="fa-solid fa-arrow-up text-success"
                                                v-if="[3, 4].includes(item.tipo)"></i>
                                        </td>
                                        <td class="text-center align-middle">@{{ item.stock_despues }}</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-secondary" v-if="item.tipo === 1">Salida</span>
                                            <span class="badge badge-warning" v-else-if="item.tipo === 2">Venta</span>
                                            <span class="badge badge-primary" v-else-if="item.tipo === 3">Ingreso</span>
                                            <span class="badge badge-info" v-else-if="item.tipo === 4">Anulacion</span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="text-center align-middle" style="width: 300px">
                                            <div style="
                                                max-width: 300px;
                                                white-space: nowrap;
                                                overflow-x: auto;
                                                display: block;
                                                padding-bottom: 6px;
                                            " v-html="item.comentario">
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">@{{ formatFecha(item.created_at) }}</td>
                                    </tr>

                                    <tr v-if="filteredHistorial.length === 0">
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No hay registros de movimientos para este producto.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <b-modal id="modal-reducir-stock" :title="stockModalTitle" ok-title="Guardar" @ok.prevent="guardarCambiosStock" size="lg"
            header-class="bg-xprimary text-white" dialog-class="modal-lg">
            <div v-if="productoStockModal">
                <h5><strong v-text="productoStockModal.codigo_barras.label"></strong> <span v-text="productoStockModal.codigo_barras.value"></span></h5>
                <h5><strong v-text="productoStockModal.nombre.label"></strong> <span v-text="productoStockModal.nombre.value"></span></h5>
            </div>

            <b-table :items="productoStockModal ? productoStockModal.tiendas : []" :fields="camposStock" bordered small responsive class="mt-3">
                <!-- Nombre de la tienda -->
                <template #cell(nombre)="data">
                    @{{ data.item.nombre }}
                </template>

                <!-- Stock actual -->
                <template #cell(stock_actual)="data">
                    @{{ data.item.stock }}
                </template>

                <!-- Variación -->
                <template #cell(variacion)="data">
                    <b-form-input type="text" v-model="data.item.variacion" @input="soloNumerosPositivos(data.item)"
                        @focus="$event.target.select()" size="sm"
                        style="display: inline-block; width:70px; text-align:right;" placeholder="0" />
                </template>

                <!-- Stock resultante -->
                <template #cell(stock_resultante)="data">
                    @{{ data.item.stock_resultante }}
                </template>

                <!-- Comentario -->
                <template #cell(comentario)="data">
                    <b-form-input type="text" v-model="data.item.comentario" placeholder="Escribe un comentario..."
                        size="sm" />
                </template>
            </b-table>
        </b-modal>
    </div>

</x-admin-layout>
<script>
    $(document).ready(function () {


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
            order: [[1, "desc"], [0, "desc"]],
            language: {
                thousands: ".",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)"
            },
            columns: [{
                data: 'id'
            },
            {
                data: 'codigo_barras',
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        if (data && data.toString().trim() !== '') {
                            return data.toString().trim();
                        }
                        return row.id ? row.id.toString() : '';
                    }
                    return data || '-';
                }
            },
            {
                data: 'nombre',
                className: 'col-nombre-producto',
                render: function (data, type, row) {
                    if (row.alias && row.alias.trim() !== '') {
                        return row.alias;
                    }
                    if (data && data.trim() !== '') {
                        return data;
                    }
                    return '-';
                }
            },
            {
                data: 'alias',
                visible: false
            },
            {
                data: 'total_stock',
                visible: false,
                render: function (data, type, row) {
                    if (data === 0 || data === "0" || row.tiene_salida === 0) {
                        return data;
                    } else {
                        return `<span class="edit-btn text-primary" style="cursor: pointer">${data}</span>`;
                    }
                },
            },
            {
                data: 'costo_unitario',
                visible: false
            },
            {
                data: 'precio_unitario'
            },

            {
                data: 'precio_minimo',
                visible: false
            },
            {
                data: 'precio_x_mayor',
                visible: false
            },
            {
                data: 'categoria_id',
                visible: false,
                render: function (data, type, row) {
                    return row.categoria_nombre;
                }
            },
            {
                data: 'created_at',
                render: function (data, type, row) {
                    return row.created_at;
                }
            },
            {
                data: 'estado',
                render: function (data, type, row) {
                    return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' :
                        '<span class="badge bg-xsecondary text-white">Inactivo</span>'
                }
            },
            {
                data: 'tiendas',
                name: 'tiendas',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let tiendaId = $('#filtroTienda').val();
                    if (tiendaId) {
                        let t = (row.tiendas || []).find(item => item.id == tiendaId);
                        let stockVal = t ? t.stock : 0;
                        if (type === 'sort' || type === 'type') {
                            return stockVal;
                        }
                        if (stockVal < 0) {
                            return `<span class="badge badge-danger" title="¡Stock Negativo! Requiere ajuste">⚠️ ${stockVal}</span>`;
                        }
                        return `<strong>${stockVal}</strong>`;
                    }
                    if (type === 'sort' || type === 'type') {
                        return parseFloat(row.total_stock) || 0;
                    }
                    if (!data || !Array.isArray(data)) return '-';
                    return data.map(t => {
                        let s = t.stock < 0 
                            ? `<span class="badge badge-danger" title="¡Stock Negativo! Requiere ajuste">⚠️ ${t.stock}</span>` 
                            : t.stock;
                        return `<strong>${t.nombre}:</strong> ${s}`;
                    }).join('<br>');
                }
            },
            {
                data: 'id',
                name: 'acciones',
                orderable: false,
                searchable: false,
                className: 'text-center d-print-none acciones',
                render: function (data, type, row) {
                    return `
                        <div class="btn-group dropleft">
                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Acciones
                            </button>
                            <div class="dropdown-menu shadow">
                                <button type="button" class="dropdown-item btn-reducir-stock">
                                    <i class="fas fa-arrow-down text-danger mr-2"></i> Reducir
                                </button>
                                <button type="button" class="dropdown-item btn-aumentar-stock">
                                    <i class="fas fa-arrow-up text-success mr-2"></i> Aumentar
                                </button>
                                <a href="{{ route('inventario.kardex.index') }}?producto_id=${row.id}" class="dropdown-item">
                                    <i class="fas fa-clipboard-list text-info mr-2"></i> Kardex
                                </a>
                                <button type="button" class="dropdown-item btn-historial-stock">
                                    <i class="fas fa-history text-secondary mr-2"></i> Historial stock
                                </button>
                                <button type="button" class="dropdown-item btn-edit">
                                    <i class="fas fa-edit text-warning mr-2"></i> Editar
                                </button>
                                <div class="dropdown-divider"></div>
                                <button type="button" class="dropdown-item btn-destroy text-danger">
                                    <i class="fas fa-trash-alt mr-2"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
            ],
            actionsButtons: false,
            alingCenter: [1, 6, 10, 11],

        });

        // Eliminar botón Imprimir
        if (table.buttons) {
            try {
                table.buttons('.buttons-print').remove();
            } catch (e) {
                $('.buttons-print').remove();
            }
        }

        // Descarga de Excel generada desde Backend en formato matriz de tiendas
        let descargandoExcel = false;
        async function descargarExcelProductos(dtBtn, node) {
            if (descargandoExcel) return;
            descargandoExcel = true;

            const $btn = node ? $(node) : $('.buttons-excel');

            // Deshabilitar botón y activar spinner oficial de DataTables
            if (dtBtn && typeof dtBtn.enable === 'function') {
                dtBtn.enable(false);
            }
            if (dtBtn && typeof dtBtn.processing === 'function') {
                dtBtn.processing(true);
            }
            $btn.prop('disabled', true).addClass('disabled processing');

            const tiendaId = $('#filtroTienda').val() || '';
            const ocultarSinStock = $('#switchOcultarSinStock').is(':checked') ? 1 : 0;
            const search = (window.productosIndexTable && typeof window.productosIndexTable.search === 'function')
                ? window.productosIndexTable.search().trim()
                : ($('#table_filter input').val() || '').trim();

            let url = '{{ route("inventario.productos.exportar_excel") }}';
            const params = new URLSearchParams();
            if (tiendaId) params.append('tienda_id', tiendaId);
            params.append('ocultar_sin_stock', ocultarSinStock);
            if (search) params.append('search', search);

            const queryString = params.toString();
            if (queryString) {
                url += '?' + queryString;
            }

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    let errMsg = 'Ocurrió un error al generar el archivo Excel.';
                    try {
                        const errJson = await response.json();
                        if (errJson && errJson.message) errMsg = errJson.message;
                    } catch (e) {
                        const errText = await response.text();
                        if (errText) errMsg = errText;
                    }
                    throw new Error(errMsg);
                }

                // Obtener nombre de archivo si vino en Content-Disposition
                const disposition = response.headers.get('Content-Disposition');
                let fileName = 'stock_productos.xlsx';
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                    if (matches != null && matches[1]) {
                        fileName = matches[1].replace(/['"]/g, '');
                    }
                }

                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = downloadUrl;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(() => window.URL.revokeObjectURL(downloadUrl), 1000);

            } catch (error) {
                console.error('Error al exportar Excel:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de exportación',
                        text: error.message || 'No se pudo generar el archivo Excel.'
                    });
                } else {
                    alert('Error: ' + (error.message || 'No se pudo generar el archivo Excel.'));
                }
            } finally {
                descargandoExcel = false;
                // Restaurar botón y quitar spinner siempre
                if (dtBtn && typeof dtBtn.processing === 'function') {
                    try { dtBtn.processing(false); } catch(e) {}
                }
                if (dtBtn && typeof dtBtn.enable === 'function') {
                    try { dtBtn.enable(true); } catch(e) {}
                }
                if (table && table.button && typeof table.button('.buttons-excel').processing === 'function') {
                    try { table.button('.buttons-excel').processing(false); } catch(e) {}
                    try { table.button('.buttons-excel').enable(true); } catch(e) {}
                }
                $btn.prop('disabled', false).removeClass('disabled processing');
                $('.buttons-excel').removeClass('processing disabled').prop('disabled', false);
            }
        }

        // Reemplazar la acción del botón Excel de DataTables por la exportación backend
        if (table.button && table.button('.buttons-excel').length) {
            table.button('.buttons-excel').action(function (e, dt, node, config) {
                descargarExcelProductos(this, node);
            });
        }
        $(document).off('click', '#table_wrapper .buttons-excel').on('click', '#table_wrapper .buttons-excel', function (e) {
            e.preventDefault();
            e.stopPropagation();
            descargarExcelProductos(null, this);
        });

        // Helper para obtener rowData de forma segura incluso en dropdowns o responsive child rows
        function getRowDataFromBtn(btn) {
            let tr = $(btn).closest('tr');
            if (tr.hasClass('child')) {
                return table.row(tr.prev('tr')).data();
            }
            return table.row(tr).data();
        }

        // Cargar tiendas en el select si no vinieron renderizadas desde Blade
        if ($('#filtroTienda option').length <= 1) {
            $.ajax({
                url: '/inventario/salidas/tiendas/listado',
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    let list = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                    let options = '<option value="">Todas las Tiendas</option>';
                    list.forEach(function (tienda) {
                        options += `<option value="${tienda.id}">${tienda.nombre}</option>`;
                    });
                    $('#filtroTienda').html(options);
                }
            });
        }

        // Filtro personalizado para ocultar productos sin stock y/o por tienda
        let ocultarSinStock = false;
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData) {
            if (settings.sTableId !== 'table') {
                return true;
            }
            const row = rowData || (window.productosIndexTable ? window.productosIndexTable.row(dataIndex).data() : null);
            if (!row) {
                return true;
            }
            const tiendaId = $('#filtroTienda').val();
            if (ocultarSinStock) {
                if (tiendaId) {
                    const t = (row.tiendas || []).find(item => item.id == tiendaId);
                    const stockEnTienda = t ? parseFloat(t.stock) || 0 : 0;
                    return stockEnTienda > 0;
                } else {
                    const totalStock = parseFloat(row.total_stock) || 0;
                    return totalStock > 0;
                }
            }
            return true;
        });

        $('#filtroTienda').on('change', function () {
            if (window.productosIndexTable) {
                window.productosIndexTable.rows().invalidate('data').draw(false);
            }
        });

        $('#switchOcultarSinStock').on('change', function () {
            ocultarSinStock = $(this).is(':checked');
            if (window.productosIndexTable) {
                window.productosIndexTable.rows().invalidate('data').draw(false);
            }
        });

        cargarStocks(); // sin parámetro

        $('#table').on('click', '.btn-edit', async function () {
            let rowData = getRowDataFromBtn(this);
            await cargarStocks(rowData.id);
            edit_record(rowData, table, $(this));
        });

        // store record
        $(document).on('click', '.btn-store', function () {

            no_send_form('#form');

            let form = document.getElementById('form');

            if (!form.checkValidity()) {
                return;
            }
            let formData = new FormData(form);

            // agrega ulid a la data
            const uid = ULID.ulid();
            formData.append("uid", uid);

            store_record(dataCrud, formData, table);

        });

        // destroy record
        $('#table').on("click", ".btn-destroy", function () {
            let rowData = getRowDataFromBtn(this);
            destroy_record(dataCrud, table, rowData);
        });

        // reducir stock
        $('#table').on('click', '.btn-reducir-stock', function () {
            let rowData = getRowDataFromBtn(this);
            if (window.productosVue) {
                window.productosVue.abrirModalStock(rowData, 'REDUCIR');
            }
        });

        // aumentar stock
        $('#table').on('click', '.btn-aumentar-stock', function () {
            let rowData = getRowDataFromBtn(this);
            if (window.productosVue) {
                window.productosVue.abrirModalStock(rowData, 'AUMENTAR');
            }
        });

        // historial stock
        $('#table').on('click', '.btn-historial-stock', function () {
            let rowData = getRowDataFromBtn(this);
            if (window.productosVue) {
                window.productosVue.abrirHistorial(rowData);
            }
        });

        //cargar select de categorias
        $.ajax({
            url: '/inventario/categoria',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
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
            success: function (data) {
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
                    success: function (data) {
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
                    error: function (xhr, status, error) {
                        reject(error); // <- en caso de error
                    }
                });
            });
        }

        // guardamos la intacia de larajax
        window.productosIndexTable = table;
    });

    Vue.use(BootstrapVue);

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
                selectedTienda: null,
                tiendas: [],
                productoStockModal: {
                    id: null,
                    codigo_barras: { label: '', value: '' },
                    nombre: { label: '', value: '' },
                    tiendas: []
                },
                variacion: 'REDUCIR',
            }
        },
        computed: {
            filteredHistorial() {
                if (!this.selectedTienda) {
                    return this.historial;
                }
                return this.historial.filter(item => item.tienda_id === this.selectedTienda);
            },
            stockModalTitle() {
                return this.variacion === 'AUMENTAR'
                    ? 'Aumentar Stock de Producto'
                    : 'Reducir Stock de Producto';
            },
            camposStock() {
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
                ];
            },
        },
        mounted() {
            window.productosVue = this;
            const vm = this;
            $('#table').on('click', '.edit-btn', function () {
                const rowData = window.productosIndexTable.row($(this).closest('tr')).data();
                vm.abrirHistorial(rowData);
            });
        },
        methods: {
            abrirHistorial(producto) {
                this.productoIdHistorial = producto.id;
                this.producto = producto;
                this.cargarHistorial();
                this.getTiendas();
                $("#modal-historial-salidas").modal("show");
            },
            abrirModalStock(row, variacion = 'REDUCIR') {
                this.variacion = variacion;
                const nombreProd = (row.alias && row.alias.trim() !== '') ? row.alias : (row.nombre || '-');
                this.productoStockModal = {
                    id: row.id,
                    codigo_barras: {
                        label: 'Código de barras:',
                        value: row.codigo_barras || '',
                    },
                    nombre: {
                        label: 'Nombre:',
                        value: nombreProd,
                    },
                    tiendas: Array.isArray(row.tiendas) ? JSON.parse(JSON.stringify(row.tiendas)) : [],
                };
                if (this.productoStockModal.tiendas.length > 0) {
                    this.productoStockModal.tiendas.forEach((t) => {
                        this.$set(t, "variacion", 0);
                        this.$set(t, "stock_resultante", t.stock);
                        this.$set(t, "comentario", "");
                    });
                    this.$bvModal.show("modal-reducir-stock");
                } else {
                    window.api.get(`/inventario/stock?producto_id=${row.id}`)
                        .then(res => {
                            const tiendasList = Array.isArray(res) ? res : (res.data || []);
                            this.productoStockModal.tiendas = tiendasList.map(t => ({
                                id: t.id,
                                nombre: t.nombre,
                                stock: t.stock || 0,
                                variacion: 0,
                                stock_resultante: t.stock || 0,
                                comentario: ''
                            }));
                            this.$bvModal.show("modal-reducir-stock");
                        })
                        .catch(err => {
                            console.error('Error cargando stock:', err);
                            this.$bvToast.toast('No se pudo cargar el stock por tienda.', {
                                title: 'Error',
                                variant: 'danger',
                                solid: true
                            });
                        });
                }
            },
            soloNumerosPositivos(tienda) {
                let valor = tienda.variacion != null ? tienda.variacion.toString() : "";
                const soloNumeros = valor.replace(/[^0-9]/g, "");
                if (soloNumeros === "") {
                    tienda.variacion = "";
                    tienda.stock_resultante = tienda.stock;
                    return;
                }
                let numero = parseInt(soloNumeros, 10);
                if (isNaN(numero) || numero < 0) {
                    numero = 0;
                }
                tienda.stock_resultante = this.variacion === 'AUMENTAR'
                    ? tienda.stock + numero
                    : tienda.stock - numero;
                tienda.variacion = numero;
            },
            guardarCambiosStock() {
                if (!this.productoStockModal || !this.productoStockModal.tiendas) return;

                const datos = this.productoStockModal.tiendas
                    .filter(t => t.stock_resultante !== t.stock)
                    .map((t) => ({
                        producto_id: this.productoStockModal.id,
                        tienda_id: t.id,
                        stock_antes: t.stock,
                        cantidad_reducida: t.variacion,
                        stock_despues: t.stock_resultante,
                        tipo: this.variacion === 'REDUCIR' ? 1 : 3,
                        comentario: t.comentario,
                    }));

                if (datos.length === 0) {
                    this.$bvModal.hide('modal-reducir-stock');
                    return;
                }

                const msg = this.variacion === 'AUMENTAR'
                    ? 'Stock aumentado correctamente.'
                    : 'Stock reducido correctamente.';

                window.api.post('/inventario/salidas/reducir', datos)
                    .then(res => {
                        this.$bvToast.toast(msg, {
                            title: 'Éxito',
                            variant: 'success',
                            solid: true
                        });
                        this.$bvModal.hide('modal-reducir-stock');
                        if (window.productosIndexTable) {
                            window.productosIndexTable.ajax.reload(null, false);
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
            async cargarHistorial() {
                if (!this.productoIdHistorial) return;
                try {
                    const res = await window.api.get(`/inventario/salidas/historial/${this.productoIdHistorial}`);
                    // window.api ya retorna response.data
                    this.historial = Array.isArray(res) ? res : (res.data || []);
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
            getTiendas() {
                // Obtener la lista de tiendas desde el servidor
                window.api.get('/inventario/salidas/tiendas/listado')
                    .then(res => {
                        // window.api ya retorna response.data
                        const dataList = res.data || res;
                        this.tiendas = [
                            {
                                id: null,
                                nombre: 'Todas las tiendas'
                            },
                            ...(Array.isArray(dataList) ? dataList : [])
                        ];
                    })
                    .catch(err => {
                        console.error('Error al obtener tiendas:', err);
                    });
            },
        },
    });
</script>