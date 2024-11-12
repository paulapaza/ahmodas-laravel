<x-admin-layout>
    <x-slot name="menu">
        <x-menuOdoocpe />
    </x-slot>
    <x-slot name="pagetitle">Purchase Odoo</x-slot>
    <x-slot name="titulo">Purchase order</x-slot>
    
    <x-table>
        <th>id</th>
        <th>name</th>
        <th>date_order</th>
        <th>partner_id</th>
        <th>amount_total</th>
        <th>amount_tax</th>
        <th>state</th>
        <th>qty lines</th>
        <th>order_lines</th>
       
    </x-table>
   

    <x-modalFechas/>

</x-admin-layout>

<script>
    $(document).ready(function() {
        csrf = $('input[name="_token"]').val();
     
        let fechaHoy = new Date().toLocaleDateString('en-CA');
        let fechaMañana = new Date(new Date().setDate(new Date().getDate() + 1))
            .toLocaleDateString('en-CA');

        cargarTabla(fechaHoy, fechaMañana);


       
        //print
        $('#table').on("click", ".btn-print", function() {
            //abrie nueva ventana
            let id = $(this).attr('id')
            window.open('/recaudos/recibo/pdfprint/' + id, '_blank');
        });
       
    });
    $('#table').on("click", ".btn-PrintEtiquetas", function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
         
            let purchase_lines_ids = rowData.order_line
            //redirect to print etiquetas
            window.location.href = '/odoocpe/barcode/purchase-order-lines/' + purchase_lines_ids;
        });
    
    function cargarTabla(fechaInicio, fechaFin) {
            dataAjax = {
                subject: 'Purchase Order',
                route: '/odoocpe/purchase-order/',
                queryParams: fechaInicio + '/' + fechaFin,
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
                        "data": "date_order"
                    },
                    {
                        "data": "partner_id"
                    },
                    {
                        "data": "amount_total",
                        "render": function(data, type, row) {
                            return data.toFixed(2);
                        }
                    },
                    {
                        "data": "amount_tax",
                        "render": function(data, type, row) {
                            return data.toFixed(2);
                        }
                    },
                    {
                        "data": "state"
                    },
                    {
                        "data": "order_line",
                        "render": function(data, type, row) {
                            return data.length;
                        }
                    },
                    {
                        "data": "order_line",
                        "render": function(data, type, row) {
                            return data;
                        }
                    }, 

                ],
                actionsButtons: {
                   // menuType: 'dropdown',
                    print: true,
                    //view: true,
                    customButton: [
                        
                        {
                            text: 'Imprimir etiquetas',
                            icon: 'fas fa-barcode',
                            action: 'PrintEtiquetas'
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
   
</script>

