<x-admin-layout>
    <x-slot name="pagetitle">Usuarios</x-slot>
    <x-slot name="menu">
        <x-menuConfiguracionGeneral></x-menuConfiguracionGeneral>
    </x-slot>

    <x-table>
        <th>id</th>
        <th>Nombre</th>
        <th>Rol</th>
        <th>Correo</th>
        <th>Estado</th>
        <th>Tipo Impresion</th>
        <th>Restriccion Precio Minimo</th>
        <th>Creado</th>

    </x-table>

    <x-mymodal>

        @csrf

        <input type="hidden" id="id" name="id">

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" pattern="[a-zA-ZáéíóúüñÑ\s]{3,255}" title="El nombre solo debe contener letras" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Rol</label>
            <select class="form-control" id="role" name="role" required>

            </select>
        </div>
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
        <div class="mb-3">
            <label for="status" class="form-label">Tipo de Impresion (TICKET)</label>
            <select class="form-control" id="print_type" name="print_type">
                <option value="pdf">Muestra PDF</option>
                <option value="red">Imprime en Red</option>

            </select>
        </div>
      
        <div class="mb-3">
            <label for="restriccion_precio_minimo" class="form-label">Restriccion Precio Minimo</label>
            <select class="form-control" id="restriccion_precio_minimo" name="restriccion_precio_minimo">
                <option value="si">Si</option>
                <option value="no">No</option>
            </select>
        </div>
    </x-mymodal>


</x-admin-layout>

<script>
    $(document).ready(function() {
        // Inicializamos las variables para el datatable y funciones
        /*  let model = {
             nombre: 'usuario', // se muestra en vista
             route: '/user',
         }; */

        let csrf = $('input[name="_token"]').val();
        let method = '';
        llenarSelectRoles()

        dataAjax = {
            subject: 'Usuario',
            route: '/user',
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
                    "data": "roles",
                    "render": function(data, type, row) {
                        if (Array.isArray(data) && data.length > 0) {
                            for (let i = 0; i < data.length; i++) {
                                if (data[i].name) {
                                    return data[i].name;
                                }
                            }
                        } else {
                            return ''; 
                        }
                    }
                },
                {
                    "data": "email"
                },
                {
                    "data": "estado",
                    "render": function(data, type, row) {
                        return (data == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                    }
                },
                {
                    "data": "print_type"
                },
                {
                    "data": "restriccion_precio_minimo",
                    "render": function(data, type, row) {
                        return (data == 'si') ? '<span class="badge bg-success">Si</span>' : '<span class="badge bg-danger">No</span>';
                    }
                },
                {
                    "data": "created_at"
                },


            ],
            actionsButtons: {
                //menuType: 'dropdown',
                edit: true,
                destroy: true,
                customButton: [{
                    text: 'Reset password',
                    icon: 'fa-solid fa-key',
                    action: 'reset-password'
                }]
            }
        });



        $('#table').on('click', '.btn-edit', function() {

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            
            $("#role" ).val(rowData.roles[0].name);
             
            edit_record(rowData, table, $(this));

        });

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
        // Destroy record
        $(document).on("click", ".btn-destroy", function() {
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            destroy_record(dataAjax, table, rowData);
        });

        // reset-password
        $('#table').on('click', '.btn-reset-password', function() {
            let user_id = $(this).attr('id');
            $.ajax({
                type: 'get',
                url: '/user/resetpassword/' + user_id,
                data: {
                    _token: csrf,
                },
                success: function(respuesta) {
                    swal_message_response(respuesta) ? table.ajax.reload() : null;
                },
                error: function(errors) {
                    console.log(errors);
                }
            })

        });

        function llenarSelectRoles() {
            $.ajax({
                type: 'GET',
                url: '/role',
                dataType: 'json',
                success: function(data) {

                    let roles = data;
                    let select = $('#role');
                    select.empty();
                    select.append('<option value="">Seleccione un rol</option>');
                    $.each(roles, function(index, role) {
                        // select.append('<option value="' + role.id + '">' + role.name + '</option>');
                        select.append('<option value="' + role.name + '">' + role.name + '</option>');
                    });
                },
                error: function(errors) {
                    console.log(errors);
                }
            });
        }

    });
</script>