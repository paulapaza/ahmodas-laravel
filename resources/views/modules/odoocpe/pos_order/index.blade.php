<x-admin-layout>
    <x-slot name="menu">
        <x-menuOdoocpe />
    </x-slot>
    <x-slot name="pagetitle">Cotizaciones Odoo</x-slot>
    <x-slot name="titulo">Cotizaciones Odoo</x-slot>
    
    <x-table>
        <th>id</th>
        <th>Nro de recibo</th>
        <th>pos_reference</th>
        <th>Fecha de venta</th>
        <th>cant de Items</th>
        <th>pos.order.lines</th>
        <th>Pago total</th>
        <th>usuario</th>
        <th>Cliente</th>
       
    </x-table>
   

    <x-modalFechas/>

</x-admin-layout>

<script>
    $(document).ready(function() {
        csrf = $('input[name="_token"]').val();
        dataAjax = {
            subject: 'Cotizacion',
            route: '/ventasOdoo',
            csrf: csrf,
        }
        let fechaHoy = new Date().toLocaleDateString('en-CA');
        let fechaMañana = new Date(new Date().setDate(new Date().getDate() + 1))
            .toLocaleDateString('en-CA');

        cargarTabla(fechaHoy, fechaMañana);

        // edit record
        $('#table').on('click', '.btn-edit', function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
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

            store_record(dataAjax, formData, table);

        });

        // destroy record
        $('#table').on("click", ".btn-destroy", function() {
            console.log('destroy')

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

            destroy_record(dataAjax, table, rowData)
        });
        //print
        $('#table').on("click", ".btn-print", function() {
            //abrie nueva ventana
            let id = $(this).attr('id')
            window.open('/recaudos/recibo/pdfprint/' + id, '_blank');
        });
        //anular recibo
        $('#table').on("click", ".btn-anular-recibo", function() {
            let id = $(this).attr('id');
            Swal.fire({
                title: 'Anular recibo',
                text: "Esta seguro de anular el recibo ID Nro: " + id,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Anular'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/recaudos/recibos/anular',
                        type: 'Post',
                        data: {
                            id: id,
                            _token: csrf
                        },
                        success: function(data) {
                            swal_message_response(data) ? table.ajax.reload() :
                                null;
                        }
                    });
                }
            })
        });
    });
    
    function cargarTabla(fechaInicio, fechaFin) {
            dataAjax = {
                subject: 'Pos Order',
                route: '/odoocpe/pos-order',
                queryParams: '/' + fechaInicio + '/' + fechaFin,
                csrf: csrf,
            }
            $customButtons = [];


            table = new Larajax({
                data: dataAjax,
                idTable: '#table',
                newRecordTopButton: false,
                linkShow: {
                    target: 2
                },
                columns: [{
                        "data": "id"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "pos_reference"
                    },
                    {
                        "data": "date_order"
                    },
                    {
                        "data": "lines",
                        "render": function(data, type, row) {
                            return data.length;
                        }
                    },
                    {
                        "data": "lines",
                        "render": function(data, type, row) {
                            return data;
                        }
                    },
                    {
                        "data": "amount_total",
                        "render": function(data, type, row) {
                            return data.toFixed(2);
                        }
                    },
                    {
                        "data": "user_id",
                        "render": function(data, type, row, meta, full) {
                            return data[1];
                        }
                    },
                    {
                        "data": "partner_id",
                        "render": function(data, type, row) {
                            if (data) {
                                return data[1];
                            } else {
                                return '';
                            }
                        }

                    },
                    

                ],
                actionsButtons: {
                   // menuType: 'dropdown',
                    //print: true,
                    //view: true,
                    customButton: [
                        @can('anular-recibo') {
                            text: 'Anular recibo',
                            icon: 'fas fa-ban',
                            action: 'anular-recibo',

                        },
                        @endcan
                        // generar cpe
                        {
                            text: 'Generar CPE',
                            icon: 'fas fa-file-invoice',
                            action: 'generar-cpe',
                        },
                    ],

                },
                alingCenter: [1, 2],
                customTopButton: [{
                    text: 'Selecione fecha',
                    icon: 'fas fa-calendar-alt',
                    myfunction: () => Selecionarfecha(),
                }],

            });
        }
    //generar cpe
    $('#table').on("click", ".btn-generar-cpe", function() {
        
      
    });
</script>

