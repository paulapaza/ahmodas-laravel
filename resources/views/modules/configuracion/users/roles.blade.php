<x-admin-layout>
    <x-slot name="pagetitle">Roles</x-slot>
    <x-slot name="menu">
        <x-menuConfiguracionGeneral/>
    </x-slot>

    <x-table>
        <th>id</th>
        <th>Nombre</th>
        <th>Permisos</th>
        <th>Acciones</th>
    </x-table>

    <x-mymodal>

        @csrf

        <input type="hidden" id="id" name="id">

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" pattern="[a-zA-ZáéíóúüñÑ\s]{3,255}" title="El nombre solo debe contener letras" required>
        </div>


    </x-mymodal>


</x-admin-layout>

<script>
    $(document).ready(function() {

        let csrf = $('input[name="_token"]').val();
        dataAjax = {
            subject: 'Rol',
            route: '/role',
            csrf: csrf,
        }
        console.log(dataAjax);
        table = new Larajax({
            data: dataAjax,
            idTable: '#table',
            columns: [{
                    "data": "id",
                    width: '20%'
                },
                {
                    "data": "name"
                },

                {
                    "data": "permissions",
                    className: 'lista-permisos',
                    render: function(data, type, row) {
                        let permissions = '';
                        $.each(data, function(index, value) {
                            permissions += '<span class="badge bg-danger mr-2">' + value.descripcion + '</span> ';
                        });
                        return permissions;
                    }
                }

            ],
            actionsButtons: {
                //menuType: 'dropdown',
                edit: true,
                destroy: true,
                customButtonLink: [{
                    text: 'Permisos',
                    url: '/role',
                    icon: 'fas fa-key',
                    "class": "btn-info",

                }]
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
            let serializedata = $('#form').serialize();
            store_record_serialize(id, dataAjax, serializedata, table);

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