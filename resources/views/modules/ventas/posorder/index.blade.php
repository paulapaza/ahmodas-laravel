<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas />
    </x-slot>
    <x-slot name="pagetitle">Ventas</x-slot>

    <x-table>

        <th>ID</th>
        <th>Tienda</th>
        <th>Tipo Doc.</th>
        <th>Serie</th>
        <th>Nro Doc</th>
        <th>Fecha</th>
        <th>Total</th>
        <th>Usuario</th>
        <th>Estado</th>

    </x-table>

    <x-mymodal>
        @csrf
        <input type="hidden" id="id" name="id">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="tienda_id">Tienda</label>
                    <select class="form-control" id="tienda_id" name="tienda_id" required>

                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="tipo_comprobante">tipo comprobante</label>
                    <select class="form-control" id="tipo_comprobante" name="tipo_comprobante" required>
                        <option value="03">Boleta</option>
                        <option value="01">Factura</option>
                        <option value="12">Ticket</option>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="serie">Serie</label>
                    <input type="text" class="form-control" id="serie" name="serie" required>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="correlativo">Correlativo</label>
                    <input type="text" class="form-control" id="correlativo" name="correlativo" required>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

    </x-mymodal>
    <x-mostrarTotal>
        suma total ventas: <span id="totalSoles" class="mr-5"></span>
    </x-mostrarTotal>

    <x-modalFechas></x-modalFechas>
</x-admin-layout>

<script>
    $(document).ready(function() {

        // token = $('input[name="_token"]').val();
        token = $('input[name="_token"]').val();
        cargarTabla();

        // suma total de la columna total_amount
        table.on('draw', function() {
            console.log('Tabla recargada');
            let total = 0;
            table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                let data = this.data();
                //si la columna estado = completado, sumar total_amount
                if (data.estado !== 'completado') {
                    return;
                }
                total += parseFloat(data.total_amount);

            });
            $('#totalSoles').text(total.toFixed(2));
        });
        //anular venta
        table.on('click', '.btn-anular-venta', function() {

        });

    });

    function cargarTabla(fechaInicio = "", fechaFin = "") {
        //destruir la tabla si ya existe
        if ($.fn.DataTable.isDataTable('#table')) {
            table.destroy();
        }
        table = new Larajax({
            data: tableParqueosParams = {
                modelName: 'Parqueo', //nombre del modelo
                route: '/ventas/posorder', //ruta del controlador generada por el resource
                modalId: '#modal',
                queryParams: '/' + fechaInicio + '/' + fechaFin,
            },
            linkShow: {
                target: 4
            },
            newRecordTopButton: false,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'tienda.nombre',
                },
                {
                    data: 'tipo_comprobante',
                    render: function(data, type, row) {
                        switch (data) {
                            case '01':
                                return '<span class="badge badge-xprimary">Factura</span>';
                            case '03':
                                return '<span class="badge badge-xsuccess">Boleta</span>';
                            case '12':
                                return '<span class="badge badge-xdanger">Ticket</span>';
                            case '07':
                                return '<span class="badge badge-xwarning">Nota de Crédito</span>';
                            case '08':
                                return '<span class="badge badge-xwarning">Nota de Débito</span>';
                            default:
                                return data;
                        }
                    }
                },
                {
                    data: 'serie',
                },
                {
                    data: 'order_number',
                },
                {
                    data: 'order_date',
                },

                {
                    data: 'total_amount',
                    name: 'total_amount',
                },
                {
                    data: 'user.name',
                },
                {
                    data: 'estado',
                    render: function(data, type, row) {
                        return data === 'anulado' ?
                            '<span class="badge badge-xsecondary">Anulado</span>' :
                            '<span class="badge badge-xsuccess">Activo</span>';
                    }
                },

            ],
            actionsButtons: {
                cancel: true, // no implementado
                /* customButton: [{
                    text: 'Anular',
                    action: 'anular-venta',
                }, */

            },
            customTopButton: [{
                text: 'Filtro: Hoy  ',
                icon: 'fas fa-calendar-alt',
                class: 'btn-select-fecha bg-xsuccess',
                myfunction: () => Selecionarfecha(),
            }],
        });
    }
</script>
