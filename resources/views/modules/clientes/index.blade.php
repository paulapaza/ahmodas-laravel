<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Clientes</x-slot>

    <x-table>
        <th>id</th>
        <th>Nombre</th>
        <th>Tipo Doc </th>
        <th>Nro Doc</th>
        <th>Ubigeo</th>
        <th>direccion</th>
        <th>Telefono</th>
        <th>Email</th>
        <th>acciones</th>
        
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
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tipo_documento_identidad">Tipo Doc Identidad</label>
                   <select name="tipo_documento_identidad" id="tipo_documento_identidad" class="form-control" required>
                       <option value="">Seleccione</option>
                       <option value="1">DNI</option>
                       <option value="6">RUC</option>
                       <option value="0">Doc.trib.no.dom.sin.ruc</option>
                       <option value="4">Carnet extranjeria</option>
                       <option value="7">Pasaporte</option>
                       <option value="E">TAM- Tarjeta Andina de Migración</option>

                   </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="numero_documento_identidad">Numero Doc Identidad</label>
                    <input type="text" class="form-control" id="numero_documento_identidad" name="numero_documento_identidad" required>
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
                    <label for="ubigeo">Ubigeo</label>
                    <input type="text" class="form-control" id="ubigeo" name="ubigeo" >
                </div>
            </div>
          
            <div class="col-md-6">
                <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
            </div>     
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
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
            route: "/ventas/cliente",
            subject: 'Cliente',
            model: "Cliente",
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
                    data: 'tipo_documento_identidad',
                    render: function(data, type, row) {
                        return data == 1 ? 'DNI' : 
                               data == 6 ? 'RUC' : 
                               data == 0 ? 'Doc.trib.no.dom.sin.ruc' : 
                               data == 4 ? 'Carnet extranjeria' : 
                               data == 7 ? 'Pasaporte' : 
                               data == 'E' ? 'TAM- Tarjeta Andina de Migración' : '';
                    }
                },
                {
                    data: 'numero_documento_identidad'

                },
                
                {
                    data: 'ubigeo'
                },
                {
                    data: 'direccion',
                    width: "20%" 
                    
                },
                {
                    data: 'telefono'
                },
                {
                    data: 'email'
                }


            ],
            actionsButtons: {
                edit: true,
                destroy:true
            }
            
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

     
        
    });
</script>
