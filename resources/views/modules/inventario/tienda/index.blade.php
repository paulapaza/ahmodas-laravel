<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Tiendas</x-slot>

    <x-table>
        <th>id</th>
        <th>Nombre</th>
        <th>Direccion</th>
        <th>Telefono</th>
        
        <th>Estado</th>

    </x-table>

    <x-mymodal>
        @csrf
        <input type="hidden" id="id" name="id">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="form-group">
                    <label for="direccion">Direccion</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
           
            <div class="col-md-12">
                <div class="form-group">
                    <label for="ticket_nota">Nota de la tienda</label>
                    <textarea class="form-control" id="ticket_nota" name="ticket_nota" placeholder="Nota de la tienda" rows="3" required></textarea>
                </div>
            </div>
       
            <div class="col-md-12">
                <div class="form-group">
                    <label for="ruta_api_facturacion">Ruta API Facturación</label>
                    <input type="text" class="form-control" id="ruta_api_facturacion" name="ruta_api_facturacion" required>
                </div>
            </div>
           
            <div class="col-md-12">
                <div class="form-group">
                    <label for="token_facturacion">Token Facturación</label>
                    <input type="text" class="form-control" id="token_facturacion" name="token_facturacion" required>
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
            route: "/inventario/tienda",
            subject: 'Tienda',
            model: "Tienda",
            csrf: csrf,
        };
        let table = new Larajax({
            data: dataCrud,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'nombre'
                },
                
                {
                    data: 'direccion'
                },
              
                {
                    data: 'telefono'
                },
                {
                    data: 'estado',
                    render: function(data) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' : '<span class="badge bg-xsecondary text-white">Inactivo</span>'
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
            
          /*   // ocultar el option de la categoria seleccionada (para que no se pueda seleccionar a si misma)
            $('#categoria_padre_id option').show();
            $('#categoria_padre_id option[value=' + rowData.id + ']').hide();
          */
           
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

     
        
    });
</script>
