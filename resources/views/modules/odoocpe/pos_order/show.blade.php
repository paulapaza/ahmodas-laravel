<x-admin-layout>
    <x-slot name="menu">
        <x-menuOdoocpe />
    </x-slot>

    

        <div class="card">
            <div class="card-header">
                Nuevo CPE
                <button class="btn btn-xsuccess float-right" id="facturar">Generar <span id="botonTipoDocumento">Boleta Electrónica</span></button>
                <button class="btn btn-xaccent float-right mr-2" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                    <span class="fas fa-plus text-light"></span>
                </button>

            </div>
            <div class="card-body ">
                <form id="frmVenta" submit="return false">
                    <input type="hidden" name="accion" id="accion" value="GUARDAR_VENTA">
                    <div class="row">
                        <div class="form-group col-md-2">
                            <label>Tipo documento</label>
                            <select name="TipoDocumento" id="TipoDocumento" class="form-control">
                                <option value="03">Boleta Electrónica</option>
                                <option value="01">Factura Electrónica</option>
                                <option value="07">Nota de crédito</option>
                                <option value="08">Nota de débito</option>
                            </select>

                        </div>
                        <div class="form-group col-md-4">
                            <label>Cliente <small class="ml-3 text-xprimary" id="nuevoCliente">[+ nuevo] </small></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="ciente" id="cliente" idpartner="{{$cliente['id']}}" value="{{$cliente['vat']??'00000000'}} - {{$cliente['name']??'Cliente varios'}}">
                                <ul id="datos">
                                </ul>
                                <div class="input-group-addon">

                                    <button type="button" class="btn btn-default bg-xgray " data-toggle="modal" data-target="#modelId">
                                        <li class="fas fa-user-plus text-xaccent" title="Buscar"></li>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>DIRECCION</label>
                            <input type="text" class="form-control" name="direccionCliente" id="direccionCliente" value="{{$cliente['state_id'][1]??''}} / {{$cliente['city']??''}} / {{$cliente['l10n_pe_district'][1]??''}} / {{$cliente['street']??''}}" disabled>
                        </div>


                    </div>
                    <!-- datos del cliente -->
                    
                </form>
                <div id="collapseExample" class="collapse">
                    <div class="row">
                        <div class="form-group col-2">
                            <label>Tipo Doc. Ident.</label>
                            <select name="TipoDocumentoIdentidad" id="TipoDocumentoIdentidad" class="form-control">

                                @foreach ($TipoDocumento as $tipodoc)
                                <option value="{{$tipodoc['codigo']}}" idodoo="{{$tipodoc['idOdoo']}}" @if ($cliente!=null && $tipodoc['codigo']==$cliente['l10n_latam_identification_type_id'][0]) selected @endif>
                                    {{$tipodoc['descripcion']}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-2">
                            <label>NRO DOC.</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="nrodoc" id="nrodoc" value="{{$cliente['vat']??'00000000'}}">
                                <div class="input-group-addon">
                                    <button class="btn btn-default" type="button" onclick="ObtenerDatosEmpresa()">
                                        <li class="fas fa-search" title="Buscar"></li>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-2">
                            <label>RAZÓN SOCIAL</label>
                            <input type="text" class="form-control" name="razon_social" id="razon_social" value="{{$cliente['name']??'Cliente varios'}}">
                        </div>
                        <div class="form-group col-1">
                            <label>departamento</label>
                            <input type="text" class="form-control" name="departamento" id="departamento" idpartamentoodoo="{{$cliente['state_id'][0]??''}}" value=" {{$cliente['state_id'][1]??''}}">
                        </div>
                        <div class="form-group col-1">
                            <label>Provincia</label>
                            <input type="text" class="form-control" name="provincia" id="provincia" idprovinciaodoo="{{$cliente['city_id'][0]??''}}" value="{{$cliente['city_id'][1]??''}}">
                        </div>
                        <div class="form-group col-1">
                            <label>Distrito</label>
                            <input type="text" class="form-control" name="distrito" id="distrito" value="@if(isset($cliente['l10n_pe_district'][1])){{$cliente['l10n_pe_district'][1]}}@else''@endif">
                        </div>
                        <div class="form-group col-1">
                            <label>Ubigeo</label>
                            <input type="text" class="form-control" name="ubigeo" id="ubigeo" value="@if(isset($cliente['l10n_pe_district'][1])){{$cliente['ubigeo']}}@else''@endif">
                        </div>
                        <div class="form-group col-2">
                            <label>DIRECCION</label>
                            <input type="text" class="form-control" name="direccion" id="direccion" value="{{$cliente['street']??''}}">
                        </div>
                    </div>
                    <form id="frmVenta" submit="return false">
                        <input type="hidden" name="accion" id="accion" value="GUARDAR_VENTA">
                        <div class="row">

                            <div class="form-group col-md-2">
                                <label>Serie</label>
                                <select name="idserie" id="idserie" class="form-control">
                                    @foreach ($serie as $item)
                                    <option value="{{$item['id']}}">{{$item['serie']}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <label>Número </label>
                                <input type="number" class="form-control" name="correlativo" id="correlativo" value="{{$serie[0]['correlativo']}}" disabled>
                            </div>
                            <div class="form-group col-md-2">
                                <label>Moneda</label>
                                <select name="moneda" id="moneda" class="form-control" disabled>
                                    <option value="PEN">Soles</option>

                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <label>Fecha Emicion</label>
                                <input type="date" class="form-control" name="fecha_emision" id="fecha_emision" value="<?php echo date('Y-m-d') ?>" disabled>
                            </div>

                            <div class="form-group col-md-2">
                                <label>Forma de pago</label>
                                <select name="forma_pago" id="forma_pago" class="form-control" disabled>
                                    <option value="Contado">Contado</option>

                                </select>
                            </div>


                        </div>

                    </form>
                </div>
            </div>

        </div>
    
    <section>
        <div class="card ">

            <div class="card-body">

                <div class="">
                    <table id="table" class="stripe hover table-sm">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>nombre de producto</th>
                                <th>cantidad</th>
                                <th>precio unitario</th>
                                <th>Impuesto</th>
                                <th>subtotal neto</th>
                                <th>subtotal incl igv</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pos_order_lines as $pos_order_line)
                            <tr>
                                <td>{{$pos_order_line['id']}}</td>
                                <td>{{$pos_order_line['full_product_name']}}</td>
                                <td>{{$pos_order_line['qty']}}</td>
                                <td>{{$pos_order_line['price_unit']}}</td>
                                <td>{{$pos_order_line['tax_ids'][0]}}</td>
                                <td>{{$pos_order_line['price_subtotal']}}</td>
                                <td>{{$pos_order_line['price_subtotal_incl']}}</td>
                            </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    </section>
    <section>

        <div class="card">

            <div class="card-body">
                <div class=" text-right">
                    <span class="text-bold mr-3">OP. GRAVADAS: </span> {{$pos_order['amount_total']-$pos_order['amount_tax'] }}<br>
                    <span class="text-bold mr-3">IGV: </span>{{$pos_order['amount_tax'] }}<br>
                    <span class="text-bold mr-3">TOTAL A PAGAR: S/.</span><span class="text-lg"> {{number_format($pos_order['amount_total'], 2) }}</span>

                </div>
            </div>

        </div>

    </section>

    <!-- Button trigger modal -->


    <!-- Modal -->
    <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header  bg-xprimary">
                    <h5 class="modal-title text-light">Nuevo Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-6">
                            <label>Tipo Doc.</label>
                            <div class="input-group">
                                <select name="TipoDocumento" id="iptTipoDocumento" class="form-control">

                                    <option value="0" idodoo="6">SIN DOCUMENTO</option>
                                    <option value="1" idodoo="5">DNI</option>
                                    <option value="4" idodoo="3">CARNET DE EXTRANJERIA</option>
                                    <option value="6" idodoo="4" selected>RUC</option>
                                    <option value="7" idodoo="2">PASAPORTE</option>
                                    <option value="F">DOC</option>
                                    <option value="G">DOCUMENTO MOD PQ</option>
                                    <option value="H">DOCUMENTO XY</option>
                                    <option value="I" odooid="13">PERMISO TEMPORAL</option>
                                </select>

                            </div>
                        </div>
                        <div class="form-group col-6">
                            <label>NRO DOC.</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="nrodoc" id="iptnrodoc" value="">
                                <div class="input-group-addon">
                                    <button class="btn btn-default" type="button" onclick="ObtenerDatosEmpresa()">
                                        <li class="fas fa-search" title="Buscar"></li>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col">
                        <label>RAZÓN SOCIAL</label>
                        <input type="text" class="form-control" name="razon_social" id="iptrazon_social" value="">
                    </div>

                    <div class="row px-1">
                        <div class="form-group col">
                            <label>departamento</label>
                            <select name="departamento" id="iptdepartamento" class="form-control">
                                <option value="">--seleccione--</option>
                            </select>
                        </div>
                        <div class="form-group col">
                            <label>Provincia</label>
                            <select name="provincia" id="iptprovincia" class="form-control">

                            </select>
                        </div>
                    </div>
                    <div class="row px-1">

                        <div class="form-group col-8">
                            <label>Distrito</label>
                            <select name="distrito" id="iptdistrito" class="form-control">
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label>Ubigeo</label>
                            <input type="number" class="form-control" name="ubigeo" id="iptubigeo" disabled>
                        </div>
                    </div>
                    <div class="form-group col">
                        <label>DIRECCION</label>
                        <input type="text" class="form-control" name="direccion" id="iptdireccion" value="{{$cliente['street']??''}}">
                    </div>

                    <div class="row px-1">
                        <div class="form-group col-5">
                            <label>Teléfono</label>
                            <input type="number" class="form-control" name="telefono" id="ipttelefono">

                        </div>
                        <div class="form-group col-7">
                            <label>Email</label>
                            <input type="mail" name="email" id="iptemail" class="form-control">

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="registrarCliente">Registrar Cliente</button>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
<script>
    $(document).ready(function() {

        var TablePosLines = $('#table').DataTable({
            //sin cuadro de busqueda
            searching: false,

            //ocultar la primera columna
            columnDefs: [{
                    targets: 0,
                    visible: false,
                    searchable: false
                },
                // cambiar la columna 6 si es dos poner 1
                {
                    targets: 4,
                    render: function(data, type, row) {
                        if (data == 2) {
                            return '18% '; //gravado 10
                        } 
                        else if (data[0] == 1) {
                            return '18%';
                        } 
                        else if (data == 3) {
                            return '0% exonerado 20';
                        } 
                        else if (data == 4) {
                            return '0% inafecto 30';
                        }else {
                            return 'error Impuesto';
                        }
                    }

                },

            ],
        });

        // cargar selec de departamentos
         $.ajax({
            type: "GET",
            url: "{{route('mostrarDepartamentos')}}",
            dataType: "json",
            success: function(response) {
                $.each(response, function(key, value) {
                    $('#iptdepartamento').append('<option value="' + value.code + '" id="' + value.id + '" >' + value.name + '</option>');
                });
            }
        });

        // cargar selec de provincias al cambiar de departamento
        $('#iptdepartamento').change(function() {
            var state_id = $(this).val();


            $('#iptprovincia').empty();
            $('#iptdistrito').empty();

            $.ajax({
                type: "POST",
                url: "{{url('provincias')}}" + '/' + state_id,
                //añadir cfrs
                data: {
                    _token: '{{csrf_token()}}'
                },
                dataType: "json",
                success: function(response) {
                    $('#iptprovincia').append('<option value="">--seleccione--</option>');
                    $.each(response, function(key, value) {
                        $('#iptprovincia').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        });
        // cargar selec de distritos al cambiar de provincia
        $('#iptprovincia').change(function() {
            var city_id = $(this).val();
            $('#iptdistrito').empty();
            console.log(city_id);
            $.ajax({
                type: "POST",
                url: "{{url('distritos')}}" + '/' + city_id,
                //añadir cfrs
                data: {
                    _token: '{{csrf_token()}}'
                },
                dataType: "json",
                success: function(response) {
                    // append option "selecione"
                    $('#iptdistrito').append('<option value="">--seleccione--</option>');
                    $.each(response, function(key, value) {
                        $('#iptdistrito').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });



                }
            });
        });
        //llenar ubigeo al cambiar de distrito
        $('#iptdistrito').change(function() {
            var distrito_id = $(this).val();
            $('#iptubigeo').empty();
           
            $.ajax({
                type: "POST",
                url: "{{url('ubigeo')}}" + '/' + distrito_id,
                //añadir cfrs
                data: {
                    _token: '{{csrf_token()}}'
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    $('#iptubigeo').val(response.code);
                }
            });
        });
        // buscar cliente mientras se escribe en in input con id cliente
        $("#cliente").keyup(function(e) {
            var string = $("#cliente").val();


            if (string.length > 4 &&
                event.keyCode != 37 &&
                event.keyCode != 38 &&
                event.keyCode != 39 &&
                event.keyCode != 40 &&
                event.keyCode != 8) {
                $.ajax({
                    type: "POST",
                    url: "{{route('buscarCliente')}}",
                    //añadir cfrs
                    data: {
                        _token: '{{csrf_token()}}',
                        string: string,
                    },
                    dataType: "html",
                    async: false,
                    success: function(respuesta) {
                        $("#datos").html(respuesta);
                    }
                });
            } else if (event.keyCode == 40) {
                //console.log("tecla abajo");
                $("#datos #1").focus();

            } else {
                $("#datos").html("");
                //limpiar datos cliente
                $('#TipoDocumentoIdentidad').val('');
                $('#nrodoc').val('');
                $('#razon_social').val('');
                $('#departamento').val('');
                $('#provincia').val('');
                $('#distrito').val('');
                $('#ubigeo').val('');
                $('#direccion').val('');
                $('#direccionCliente').val('');

            }
        });
        // registrar cliente
        $('#registrarCliente').click(function() {
            console.log('registrar cliente')
            /// en odoo dni = 5, ruc = 6
            //let pos_order_id = "{{$pos_order['id']}}";
            let TipoDocumento = $('#iptTipoDocumento').find(':selected').attr('idodoo')
            let nrodoc = $('#iptnrodoc').val();
            let razon_social = $('#iptrazon_social').val();
            let id_departamento = $('#iptdepartamento').find(':selected').attr('id')
            let id_provincia = $('#iptprovincia').val();
            let id_distrito = $('#iptdistrito').val();
            let ubigeo = $('#iptubigeo').val();
            let direccion = $('#iptdireccion').val();
            let telefono = $('#ipttelefono').val();
            let email = $('#iptemail').val();

            $.ajax({
                type: "POST",
                url: "{{route('nuevoCliente')}}",
                //añadir cfrs
                data: {
                    _token: '{{csrf_token()}}',
                    pos_order: "{{$pos_order['id']}}",
                    TipoDocumento: TipoDocumento,
                    nrodoc: nrodoc,
                    razon_social: razon_social,
                    id_departamento: id_departamento,
                    id_provincia: id_provincia,
                    id_distrito: id_distrito,
                    ubigeo: ubigeo,
                    direccion: direccion,
                    telefono: telefono,
                    email: email,

                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cliente registrado correctamente',
                            html: response.mensaje,
                            //showConfirmButton: false,
                            // timer: 1500
                        })
                        $('#modelId').modal('hide');
                        $('#cliente').val(nrodoc + ' - ' + razon_social);
                        $('#direccionCliente').val($('#iptdepartamento').find(':selected').text() + ' / ' + $('#iptprovincia').find(':selected').text() + ' / ' + $('#iptdistrito').find(':selected').text() + ' / ' + direccion);

                        $('#TipoDocumentoIdentidad').val($('#iptTipoDocumento').val());
                        $('#nrodoc').val(nrodoc);
                        $('#razon_social').val(razon_social);
                        $('#departamento').val($('#iptdepartamento').find(':selected').text());
                        $('#provincia').val($('#iptprovincia').find(':selected').text());
                        $('#distrito').val($('#iptdistrito').find(':selected').text());
                        $('#ubigeo').val(ubigeo);
                        $('#direccion').val(direccion);



                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.title,
                            text: response.mensaje,
                        })
                    } 
                },
                // si la respuesta es 402
                error: function(xhr, status, error) {
                    console.log(error);
                   /*  var err = eval("(" + xhr.responseText + ")");


                    let errores = '';
                    $.each(err.errors, function(key, value) {
                        errores += value + '<br>';
                    });

                    errores = errores.replace(/,/g, '<br>');

                    Swal.fire({
                        icon: 'error',
                        title: 'Los siguientes campos no estan correctos',
                        html: errores,
                    })*/
                } 
            });
        });
        // cargar correlativo al seleccionar una opcion 
        $('#idserie').change(function() {

            let id_serie_correlativo = $(this).val();
            $.ajax({
                type: "POST",
                url: "{{route('getCorrelativo')}}",
                data: {
                    id: id_serie_correlativo,
                    _token: '{{csrf_token()}}'
                },
                success: function(response) {

                    $('#correlativo').val(response);
                }
            });
        });
        //caragar serie y correlativo al cambiar TipoDocumento
        $('#TipoDocumento').change(function() {
            let TipoDocumento = $(this).val();
            $('#botonTipoDocumento').html($('#TipoDocumento').find(':selected').text());

            $.ajax({
                type: "POST",
                url: "{{route('getSerieCorrelativo')}}",
                data: {
                    tipo_doc: TipoDocumento,
                    _token: '{{csrf_token()}}'
                },
                success: function(response) {
                    console.log(response);
                    //vaciar idserie
                    $('#idserie').empty();
                    //llenar idserie
                    $.each(response, function(key, value) {
                        $('#idserie').append('<option value="' + value.id + '">' + value.serie + '</option>');
                    });

                }
            });
        });
        // comunes

        $('#datos').on('click', 'li', function() {
            console.log($(this).attr('idPartner'));
            let name = $(this).text();
            $("#cliente").val(name);
            $("#datos").html("");
            llenarDatosCliente($(this).attr('idPartner'));

        })
        // llenar datos cliente luego  de la busqueda
        function llenarDatosCliente(id_cliente) {
            console.log(id_cliente);
            $.ajax({
                type: "POST",
                url: "{{route('obtenerDatosCliente')}}",
                data: {
                    id_cliente: id_cliente,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    //response = response[0];
                    //llenar el id del cliente
                    $('#cliente').attr('idPartner', response.id);
                    $('#TipoDocumentoIdentidad').val(response.l10n_latam_identification_type_id[0]);
                    $('#nrodoc').val(response.vat);
                    $('#razon_social').val(response.name);
                    $('#departamento').val(response.state_id[1]);
                    $('#provincia').val(response.ciudad);
                    $('#distrito').val(response.l10n_pe_district[1]);
                    $('#ubigeo').val(response.ubigeo);
                    $('#direccion').val(response.street);
                    $('#direccionCliente').val(response.state_id[1] + ' / ' + response.ciudad + ' / ' + response.l10n_pe_district[1] + ' / ' + response.street);
                }
            })
        }
        $('#facturar').on('click', function() {

            //CLIENTE
            /// genera erro pr que se esta envienado siempre
            // el mismo cliente asigando sin poder cambiar el tipo de docuemnto de identidad
            // cuando uno cambia el cliente tien que cambiar el tipo de documento de identidad
            
            
            // comprobar si el tipo de documento es factura el cliente tiene que tener ruc
            if ($('#TipoDocumento').val() == '01' && $('#TipoDocumentoIdentidad').val() != '6') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Para facturas el cliente tiene que tener RUC',
                })
                return false;
            }

            // comprobar si el tipo de documento de identidad es ruc , el documento tiene que tener 11 digitos
            if ($('#TipoDocumentoIdentidad').val() == '6' && $('#nrodoc').val().length != 11) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El RUC tiene que tener 11 digitos',
                })
                return false;
            }

            let cliente = {
                id: $('#cliente').attr('idPartner'),
                tipodoc: $('#TipoDocumentoIdentidad').val(),
                nrodoc: $('#nrodoc').val(),
                razon_social: $('#razon_social').val(),
                direccion: $('#direccion').val(),
                departamento: $('#departamento').val(),
                provincia: $('#provincia').val(),
                distrito: $('#distrito').val(),
                ubigeo: $('#ubigeo').val(),
                telefono: $('#telefono').val(),
                email: $('#email').val(),
                pais: 'PE',
            }
            let cpe = {
                TipoDocumento: $('#TipoDocumento').val(),
                serie_id: $('#idserie').val(),
                correlativo: $('#correlativo').val(),
                moneda: $('#moneda').val(),
                fecha_emision: $('#fecha_emision').val(),
                forma_pago: $('#forma_pago').val(),
                monto_pendiente: 0.00,
            }

            let pos_lines = [];
            let i = 0;
            TablePosLines.rows().eq(0).each(function(index) {
                var data = TablePosLines.row(index).data();
                const line = {
                    positem: index + 1,
                    pos_order_line: data[0],
                    productName: data[1],
                    qty: data[2],
                    priceUnit: data[3],
                    codigoAfectacion: data[4],
                    priceSubtotal: data[5],
                    priceSubtotalIncl: data[6],
                };
                pos_lines.push(line);
            });


            let pos_order = {
                pos_order: "{{$pos_order['id']}}",
                // si existe cliente en odoo enviar el id , si no existe enviar 0
                partner_id: "{{$cliente['id']??0}}",
                amount_tax: "{{$pos_order['amount_tax']}}",

                amount_total: "{{$pos_order['amount_total']}}",
            }

            $.ajax({
                type: "POST",
                url: "{{route('registrarFactura')}}",
                data: {
                    cliente: cliente,
                    cpe: cpe,
                    pos_order: pos_order,
                    pos_lines: pos_lines,
                    _token: '{{csrf_token()}}'
                },
                dataType: "json",
                success: function(response) {

                    if (response.success== true) {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            html:  response.descripcion +'<br><br>'+ response.notas+`<br><hr><div class="text-center">Imprimir</div>
                            <div class="row text-center py-3 mx-0 printInvoiceSelector">
                                <div class="col-4">
                                    <i class="fa-solid fa-receipt fa-2xl"></i>
                                    <a href="/invoice/pdf/ticket/`+response.invoice.id+`" class="btn btn-primary printInvoice" target="_blank">Ticket</a>

                                </div>
                                <div class="col-4">
                                    <i class="fa-solid fa-file-invoice fa-2xl"></i>
                                    <a href="/invoice/pdf/A4/`+response.invoice.id+`" class="btn btn-primary printInvoice" target="_blank" >Papel A4</a>
                                </div>
                                <div class="col-4">
                                    <i class="fa-solid fa-file-invoice fa-2xl"></i>
                                    <a href="/invoice/pdf/A5/`+response.invoice.id+`" class="btn btn-primary ">Papel A5</a>
                                </div>
                                </div>`,
                            // sin boton
                            showConfirmButton: false,   
                        })

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.code,
                            text: response.message,
                        })
                    }
                },



            });

        });
        // detectar  printInvoiceSelector
        $(".printInvoiceSelector").on('click', '.printInvoice', function() {
            //redireccionar a la ruta ventasOdoo
            window.location.href = "{{route('odoocpe.pos_order.index')}}";
        });
      



    });

    function switchFocus(key, current) {
        console.log(key.code);
        nroli = $("#datos li").length;
        if (key.code == "ArrowDown" && nroli > current) document.getElementById(current + 1).focus();
        else if (key.code == "ArrowUp" && current > 1) document.getElementById(current - 1).focus();
        else if (key.code == "NumpadEnter") {
            let name = $('#datos #' + current).text();
            $("#cliente").val(name);
            $("#cliente").focus();
            $("#datos").html("");
            //Llenar datos del cliente, si es que existe

        } else if (key.code == "ArrowUp" && current == 1) {
            $("#cliente").focus();
        }
    }
</script>