<x-admin-layout>
<x-slot name="pagetitle">Roles</x-slot>
    <x-slot name="menu">
        <x-menuConfiguracionGeneral></x-menuConfiguracionGeneral>
    </x-slot>
   
    <x-table>
        <th>id</th>
        <th>Internal-name</th>
        <th>Descripcion</th>
        {{-- <th>created_at</th>
        <th>updated_at</th> -->
        <th>Acciones</th> --}}
    </x-table>
        
    <x-mymodal>
        
        @csrf
       
        <input type="hidden" id="id" name="id">

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name"
                pattern="[a-zA-ZáéíóúüñÑ\s\-]{3,255}" title="El nombre solo debe contener letras"
                disabled>
         
            <label for="descripcion" class="form-label">Descripción</label>

            <input type="text" class="form-control" id="descripcion" name="descripcion"
                pattern="[a-zA-ZáéíóúüñÑ\s\-]{3,255}" title="La descripción solo debe contener letras"
                required>

        </div>
       
        
    </x-mymodal>    

    
</x-admin-layout>

<script>
    $(document).ready(function() {
        let csrf = $('input[name="_token"]').val();
        dataAjax = {
            subject: 'Permisos',
            route: '/permission',
            csrf: csrf,
        }
       table = new Larajax({
            data: dataAjax,
            idTable: '#table',
            columns: [{
                    "data": "id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "descripcion"
                }
            ],
            actionsButtons: {
                //menuType: 'dropdown',
                edit: true,
                destroy: true,
               
            }
        });
        ///edit
        $('#table').on('click', '.btn-edit', function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            edit_record(rowData, table, $(this));

        });
        // store
        $(document).on('click', '.btn-store', function() {

            no_send_form('#form');

            let form = document.getElementById('form');
            if (!form.checkValidity()) {
                return;
            }
            let id = $(this).attr('id');
            /* let serializedata = $('#form').serialize();
            store_record_serialize(id, dataAjax, serializedata, table); */
            let formData = new FormData(form);
            // console.log(formData);

            store_record(dataAjax, formData, table);

        });
        // destroy
        $('#table').on('click', '.btn-destroy', function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            destroy_record(dataAjax, table, rowData);
        });
 

    });
</script>
