<x-admin-layout>
    <x-slot name="menu">
        <x-menuConfiguracionGeneral></x-menuConfiguracionGeneral>
    </x-slot>
    <x-slot name="titulo">Usuarios</x-slot>
    <x-table>
        <th>id</th>
        <th>Nombre</th>
        <th>Username</th>
        <th>Correo</th>
        <th>Estado</th>
        <th>Creado</th>
       
    </x-table>

    <x-mymodal>

        @csrf

        <input type="hidden" id="id" name="id">

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" pattern="[a-zA-ZáéíóúüñÑ\s]{3,255}"
                title="El nombre solo debe contener letras" required>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" pattern="[a-zA-ZáéíóúüñÑ_]{3,255}"
                title="El UserName solo debe contener letras y guiones bajos" required>
        </div>
       {{--  <div class="mb-3">
            <label for="role" class="form-label">Rol</label>
            <input type="text" class="form-control" id="role" name="role" pattern="[a-zA-ZáéíóúüñÑ]{3,30}"
                title="El Rol solo debe contener letras sin espacios" required>
        </div> --}}
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-control" id="estado" name="estado" required>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Correo</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

    </x-mymodal>


</x-admin-layout>

<script>
    $(document).ready(function() {
        // Inicializamos las variables para el datatable y funciones
       

        let csrf = $('input[name="_token"]').val();
      
        let dataCrud = {
            route : "/configuracion/user",
            subject: 'Usuario',
            model: "User",
            csrf: csrf,
        };
        // Iniciamos el datatable
        /*  let table = $('#table').DataTable({
             autoWidth: false,
             dom: 'Bfrtip',
             "deferRender": true,
             
             "buttons": [{
                     text: 'Crear ' + model.nombre,
                     className: 'text-white bg-custom-primary',
                     action: function(e, dt, node, config) {
                         method = 'POST';
                         create_New_Record(model.route, model);
                     }
                 },
                 data_Table_Top_Button(model)
             ],
             "ajax": {
                 "url": model.route,
                 "dataSrc": "",
               
                 

             },
             "columns": [{
                     "data": "id"
                 },
                 {
                     "data": "name"
                 },
                 {
                     "data": "username"
                 },
                 {
                     "data": "role"
                 },
                 {
                     "data": "email"
                 },
                 {
                     "data": "status",
                     "render": function(data, type, row) {
                         return (data == 1) ? 'Activo' : 'Inactivo';
                     }
                 },
                 {
                     "data": "created_at"
                 },
                 {
                     "data": "id"
                 },
             ],
             "columnDefs": [{
                     "targets": [0, -1],
                     'className': 'text-center',

                 },
                 {
                     "targets": [1],
                     "render": function(data, type, row) {
                         return `<a href="${model.route}/${row['id']}" class="text-custom-primary">${data}</a>`;
                     }
                 },
                 render_actions_Buttons({
                     "model": model,
                     "view": true, //default false
                     "edit": true,
                     "destroy": true,
                 })
             ],
             "language": {
                 "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
             }
         }); */


        let table = new Larajax({
            data: dataCrud,
            columns: [{
                    "data": "id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "username"
                },
               /*  {
                    "data": "role"
                }, */
                {
                    "data": "email"
                },
                {
                    "data": "estado",
                    "render": function(data, type, row) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
                    }
                },
                {
                    "data": "created_at",
                    type: 'datetime',
                    render: function(data) {
                        //devolver fechi sin usar moment
                        return new Date(data).toLocaleString();
                        
                    }
                  
                    
                },
                
                
            ],

            actionsButtons:{
                //menuType: "dropdown",
                edit: true,
                destroy: true,
            },
            alingCenter: [4,5],


        })
        
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
        
            store_record(dataCrud, formData, table);

        });

        // destroy record
        $('#table').on("click", ".btn-destroy", function() {
        

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

            destroy_record(dataCrud, table, rowData)
        });

        // custom button contrato
       /*  $('#table').on("click", ".btn-contrato", function() {
            //open new tab
            let stand_id = $(this).attr('id');
            let url = '/stands/create-contrato-alquiler/' + stand_id ;
            window.open(url, '_blank');

        });
         */

    });
</script>
