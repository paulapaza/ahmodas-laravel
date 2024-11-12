<x-admin-layout>
    <x-slot name="menu">
        <x-menuOdoocpe />
    </x-slot>

    <div>Base de datos Odoo</div>

    <x-table>
        <th>id</th>
        <th>Nombre Db</th>
        <th>Url</th>
        <th>Username</th>
        <th>Password</th>
        <th>Estado</th>
        <th>Acciones</th>
    </x-table>
    <x-mymodal>
        <x-slot name="size">modal-lg</x-slot>
        @csrf
        <input type="hidden" id="id" name="id">
        <div class="row">
            <div class="mb-3 col-6">
                <label for="name" class="form-label">Nombre de la base de datos</label>
                <input type="text" class="form-control" id="name" name="name"
                    pattern="[0-9a-zA-ZáéíóúüñÑ\s]{3,30}" title="El nombre solo debe contener letras" required>
            </div>
            <div class="mb-3 col-6">
                <label for="url" class="form-label">Url de la base de datos</label>
                <input type="url" class="form-control" id="url" name="url" title="url" required>
            </div>
            <div class="mb-3 col-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3 col-6">
                <label for="password" class="form-label">Password</label>
                <input type="text" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 col-6">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-control" id="estado" name="estado" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>


    </x-mymodal>


</x-admin-layout>

<script>
    $(document).ready(function() {
        let csrf = $('input[name="_token"]').val();
        let method = '';


        dataAjax = {
            subject: 'DBodoo',
            route: '/odoocpe/configuracion/odoo-db',
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
                    "data": "url"
                },

                {
                    "data": "username",

                },
                {
                    "data": "password"
                },
                {
                    "data": "estado",
                    "render": function(data, type, row) {
                        return (data == 1) ? '<span class="badge bg-success">Activo</span>' :
                            '<span class="badge bg-secondary">Inactivo</span>';
                    }
                },

            ],
            actionsButtons: {
                //menuType: 'dropdown',
                edit: true,
                destroy: true
            }
        });



        $('#table').on('click', '.btn-edit', function() {

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

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



    });
</script>
