<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas />
    </x-slot>
    <x-slot name="pagetitle">Cambios y Devoluciones</x-slot>

    <x-table>
        <th>ID</th>
        <th>Fecha</th>
        <th>Tienda</th>
        <th>Usuario</th>
        <th>Tipo Mov.</th>
        <th>Devuelto (S/)</th>
        <th>Nuevo (S/)</th>
        <th>Diferencia (S/)</th>
        <th>Método Pago</th>
        <th>Opciones</th>
    </x-table>

    <!-- Modal Detalle -->
    <div class="modal fade" id="modalDetalleDevolucion" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detalles de la Transacción #<span id="detalle-id"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <h5 class="text-danger"><i class="fa-solid fa-arrow-right-to-bracket"></i> Entraron (Devueltos)</h5>
                            <table class="table table-sm table-striped" id="table-detalle-devueltos">
                                <thead class="bg-dark text-white">
                                    <tr><th>Cant</th><th>Producto</th><th>P. Unit</th><th>Subtotal</th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-success"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salieron (Nuevos)</h5>
                            <table class="table table-sm table-striped" id="table-detalle-nuevos">
                                <thead class="bg-dark text-white">
                                    <tr><th>Cant</th><th>Producto</th><th>P. Unit</th><th>Subtotal</th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <p><strong>Motivo/Nota:</strong> <span id="detalle-motivo"></span></p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

<script>
    $(document).ready(function() {
        let token = $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content');
        
        cargarTabla();

        // Boton para ver detalle
        $(document).on('click', '.btn-ver-detalle', function() {
            let id = $(this).data('id');
            $.get(`/ventas/devoluciones/${id}`, function(res) {
                $('#detalle-id').text(res.id);
                $('#detalle-motivo').text(res.motivo || 'Ninguno especificado');
                
                let tbodyDevueltos = $('#table-detalle-devueltos tbody').empty();
                let tbodyNuevos = $('#table-detalle-nuevos tbody').empty();

                res.detalles.forEach(d => {
                    let barcode = d.producto.codigo_barras ? `<br><small class="text-muted"><i class="fa-solid fa-barcode"></i> ${d.producto.codigo_barras}</small>` : '';
                    let html = `<tr>
                        <td>${d.cantidad}</td>
                        <td>${d.producto.nombre} ${barcode}</td>
                        <td>${parseFloat(d.precio_unitario).toFixed(2)}</td>
                        <td>${parseFloat(d.subtotal).toFixed(2)}</td>
                    </tr>`;

                    if(d.tipo_item === 'devuelto') {
                        tbodyDevueltos.append(html);
                    } else {
                        tbodyNuevos.append(html);
                    }
                });

                if(tbodyNuevos.children().length === 0) {
                    tbodyNuevos.append('<tr><td colspan="4" class="text-center text-muted">Ninguno</td></tr>');
                }
                if(tbodyDevueltos.children().length === 0) {
                    tbodyDevueltos.append('<tr><td colspan="4" class="text-center text-muted">Ninguno</td></tr>');
                }

                $('#modalDetalleDevolucion').modal('show');
            }).fail(function() {
                Swal.fire({icon: 'error', text: 'Error al cargar los detalles.'});
            });
        });
    });

    function cargarTabla() {
        if ($.fn.DataTable.isDataTable('#table')) {
            table.destroy();
        }
        
        table = new Larajax({
            data: {
                modelName: 'Devolucion',
                route: '/ventas/devoluciones',
                modalId: '#modalDetalleDevolucion', // Este modal no es de edicion, así que deshabilitamos btn de crear
            },
            newRecordTopButton: false, // Ocultar boton "+ Nuevo"
            actionsButtons: {
                edit: false,
                delete: false,
                show: false
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'created_at',
                    render: function(data) {
                        return new Date(data).toLocaleString();
                    }
                },
                { data: 'tienda.nombre' },
                { data: 'user.name' },
                { 
                    data: 'tipo_movimiento',
                    render: function(data) {
                        return data === 'cambio' ? '<span class="badge badge-warning">Cambio</span>' : '<span class="badge badge-info">Solo Devolución</span>';
                    }
                },
                { data: 'monto_devolucion', render: (data) => parseFloat(data).toFixed(2) },
                { data: 'monto_nuevo', render: (data) => parseFloat(data).toFixed(2) },
                { 
                    data: 'monto_diferencia', 
                    render: function(data) {
                        let diff = parseFloat(data);
                        if (diff > 0) return `<span class="text-success font-weight-bold">+${diff.toFixed(2)}</span>`;
                        if (diff < 0) return `<span class="text-danger font-weight-bold">${diff.toFixed(2)}</span>`;
                        return '0.00';
                    }
                },
                { data: 'metodo_pago' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<button class="btn btn-sm btn-info btn-ver-detalle" data-id="${row.id}"><i class="fa-solid fa-eye"></i> Detalle</button>`;
                    }
                }
            ],
            customTopButton: []
        });
    }
</script>
