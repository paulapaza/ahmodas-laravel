<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Series y correlativo de los Documento de Pago electronico</x-slot>

    <x-table>
        <th>id</th>
        <th>tienda</th>
        <th>tipo_comprobante</th>
        <th>serie</th> 
        <th>correlativo</th>
        <th>estado</th>

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
                    <label for="codigo_tipo_comprobante">tipo comprobante</label>
                    <select class="form-control" id="codigo_tipo_comprobante" name="codigo_tipo_comprobante" required>
                        <option value="03">Boleta</option>
                        <option value="01">Factura</option>
                        <option value="12">Ticket</option>
                        <option value="07">Nota de crédito</option>
                        <option value="08">Nota de débito</option>
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

</x-admin-layout>
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    $(document).ready(function() {

        let csrf = $('input[name="_token"]').val();
        // Inicializamos las variables para tipo de envio por ajax en Store record
        let dataCrud = {
            route: "/facturacion/cpe-serie",
            subject: 'CpeSerie',
            model: "CpeSerie",
            csrf: csrf,
        };
        let table = new Larajax({
            data: dataCrud,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'tienda.nombre'
                },
                
                {
                    data: 'codigo_tipo_comprobante',
                    render: function(data, type, row) {
                        return (data == "01") ? '<span class="badge badge-xprimary">Factura</span>' :
                            (data == "12") ? '<span class="badge badge-xdanger">Ticket</span>' :
                            (data == "03") ? '<span class="badge badge-xsuccess ">Boleta</span>' :
                            (data == "07") ? '<span class="badge badge-xwarning">Nota de crédito</span>' :
                            (data == "08") ? '<span class="badge badge-xinfo ">Nota de débito</span>' :
                            '<span class="badge bg-xdanger text-white">Otro</span>';
                    }
                },
                {
                    data: 'serie'
                },
                {
                    data: 'correlativo'
                    
                },
                {
                    data: 'estado',
                    render: function(data) {
                        return (data == "activo") ? '<span class="badge bg-xsuccess">Activo</span>' : '<span class="badge bg-xsecondary text-white">Inactivo</span>'
                    }
                },

            ],
            actionsButtons: {
                edit: true,
                destroy:true
            },
            alingCenter: [3]
        })

       
        // edit record

        $('#table').on('click', '.btn-edit', function() {
           // cargarCategorias();
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            
            // ocultar el option de la categoria seleccionada (para que no se pueda seleccionar a si misma)
            $('#categoria_padre_id option').show();
            $('#categoria_padre_id option[value=' + rowData.id + ']').hide();
            // buscar la categoria padre en la columna de la tabla
            
          /*   table.rows().data().each(function(element) {
                    $('#categoria_padre_id option[value=' + element.id + ']').hide();
            }); */
           
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
            //store_record(dataCrud, formData, table) == true ? cargarCategorias() : console.log('error, no cargo');
            store_record(dataCrud, formData, table);
        });

        // destroy record
        $('#table').on("click", ".btn-destroy", function() {
          

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

            destroy_record(dataCrud, table, rowData)
        });
        // llenar el select de tienda
        $.ajax({
            url: '/inventario/tienda',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let select = $('#tienda_id');
                select.empty();
                $.each(data, function(index, tienda) {
                    select.append($('<option>', {
                        value: tienda.id,
                        text: tienda.nombre
                    }));
                });
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar las tiendas:', error);
            }
        });
     
        
    });
</script>
