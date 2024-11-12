<x-admin-layout>
    <x-slot name="menu">
        <x-menuConfiguracionGeneral></x-menuConfiguracionGeneral>
    </x-slot>
    <x-slot name="pagetitle">Empresa</x-slot>

    {{-- <x-pageBody> --}}
    <div class="row">
        <div class="col-12 col-xl-8 container">
            <div class="card">
                <div class="card-header bg-custom-gray">
                    <h3 class="card-title">

                        Datos Comerciales de la Empresa
                    </h3>
                </div>
                <div class="card-body">


                    <form id="datosComerciales">
                        <div class="row">

                            <div class="col-12 col-md-6">

                                <div class="mb-3">
                                    @csrf
                                    <label for="name" class="form-label">Nombre Comercial</label>
                                    <input type="text" class="form-control" id="nombre_comercial"
                                        name="nombre_comercial" required value="{{ $empresa->nombre_comercial }}">
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion_comercial" class="form-label">Descripción Comercial</label>
                                    <input type="text" class="form-control" id="descripcion_comercial"
                                        name="descripcion_comercial" required
                                        value="{{ $empresa->descripcion_comercial }}">
                                </div>

                                <div class="mb-3">
                                    <label for="direccion_comercial" class="form-label">Dirección Comercial</label>
                                    <input type="text" class="form-control" id="direccion_comercial"
                                        name="direccion_comercial" value="{{ $empresa->direccion_comercial }}" required>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="website" class="form-label">Direccion Web - Tienda
                                                online</label>
                                            <input type="text" class="form-control" id="website" name="website"
                                                value="{{ $empresa->website }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="facturacion_electronica" class="form-label">Facturacion
                                                Electronica</label>
                                            <select class="form-control" id="facturacion_electronica"
                                                name="facturacion_electronica" required>
                                                <option value="NO"
                                                    @if ($empresa->facturacion_electronica == 'NO') selected @endif>NO
                                                </option>
                                                <option value="SI"
                                                    @if ($empresa->facturacion_electronica == 'SI') selected @endif>SI
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <div class="col-12 col-md-6">


                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono"
                                                pattern="[0-9]{3,255}" title="El teléfono solo debe contener números"
                                                value="{{ $empresa->telefono }}" required>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Correo</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ $empresa->email }}" required>
                                        </div>

                                    </div>
                                </div>


                                <div class="form-group">
                                    <label for="exampleInputFile">Logotipo del sistema</label>
                                    <div class="input-group mb-3">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="exampleInputFile">
                                            <label class="custom-file-label" for="exampleInputFile">selecciones su
                                                archivo
                                                de imagen</label>
                                        </div>

                                    </div>
                                    <center>

                                        @if ($empresa->logo)
                                            <img src="{{ asset('/img/' . $empresa->logo) }}" alt="..."
                                                class="img-thumbnail" width="150" height="150">
                                        @else
                                            <img src="{{ asset('/img/150.png') }}" alt="..." class="img-thumbnail">
                                        @endif
                                    </center>
                                </div>
                            </div>

                        </div>

                        <div class="row justify-content-end d-flex">
                            <button type="submit" class="btn btn-xsuccess btn-save"
                                nombre="datosComerciales">Guardar</button>

                        </div>


                    </form>
                </div>
            </div>
            <div class="card facturacion_electronica_data">
                <div class="card-header bg-custom-gray text-dark">
                    <h3 class="card-title">
                        Datos Tributarios de la Empresa
                    </h3>
                </div>
                <div class="card-body">

                    <form id="datosTributarios">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-6">

                                <div class="mb-3">
                                    <label for="razon_social" class="form-label">Razon Social</label>
                                    <input type="text" class="form-control" id="razon_social" name="razon_social"
                                        value="{{ $empresa->razon_social }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tipo_documento" class="form-label">Tipo de documento</label>
                                    <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                        <option value="1" @if ($empresa->tipo_documento == 1) selected @endif>DNI
                                        </option>
                                        <option value="6" @if ($empresa->tipo_documento == 6) selected @endif>RUC
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="nro_documento" class="form-label">R.U.C.</label>
                                    <input type="text" class="form-control" id="nro_documento"
                                        name="nro_documento" pattern="[0-9]{11,11}"
                                        title="El RUC debe contener 11 números" value="{{ $empresa->nro_documento }}"
                                        required>
                                </div>


                                <!-- input direccion de la empresa -->
                                <div class="mb-3">
                                    <label for="direccion_fiscal" class="form-label">Dirección fiscal</label>
                                    <input type="text" class="form-control" id="direccion_fiscal"
                                        name="direccion_fiscal" value="{{ $empresa->direccion_fiscal }}" required>
                                </div>



                            </div>

                            <div class="col-12 col-md-6">
                                <!-- input tipo documento identidad -->

                                <!-- input pais -->
                                <div class="mb-3">
                                    <label for="pais" class="form-label">País</label>
                                    <input type="text" class="form-control" id="pais" name="pais"
                                        required value="{{ $empresa->pais }}" disabled>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <!-- input departamento -->
                                        <div class="mb-3">
                                            <label for="departamento" class="form-label">Departamento</label>
                                            <input type="text" class="form-control" id="departamento"
                                                name="departamento" pattern="[a-zA-ZáéíóúüñÑ\s]{3,255}"
                                                title="El departamento solo debe contener letras"
                                                value="{{ $empresa->departamento }}" required>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <!-- input provincia -->
                                        <div class="mb-3">
                                            <label for="provincia" class="form-label">Provincia</label>
                                            <input type="text" class="form-control" id="provincia"
                                                name="provincia" pattern="[a-zA-ZáéíóúüñÑ\s]{3,255}"
                                                title="La provincia solo debe contener letras"
                                                value="{{ $empresa->provincia }}" required>
                                        </div>
                                    </div>
                                </div>


                                <!-- input tipo distrito -->
                                <div class="mb-3">
                                    <label for="distrito" class="form-label">Distrito</label>
                                    <input type="text" class="form-control" id="distrito" name="distrito"
                                        pattern="[a-zA-ZáéíóúüñÑ\s]{3,255}"
                                        title="El distrito solo debe contener letras"
                                        value="{{ $empresa->distrito }}" required>
                                </div>
                                <!-- input ubigeo -->
                                <div class="mb-3">
                                    <label for="ubigeo" class="form-label">Ubigeo</label>
                                    <input type="text" class="form-control" id="ubigeo" name="ubigeo"
                                        pattern="[0-9]{6,6}" title="El ubigeo debe contener 6 números"
                                        value="{{ $empresa->ubigeo }}" required>
                                </div>


                            </div>

                        </div>
                        <div class="row justify-content-end d-flex">
                            <button type="submit" class="btn btn-xsuccess btn-save"
                                nombre="datosTributarios">Guardar</button>
                        </div>


                    </form>
                </div>
            </div>
            <div class="card facturacion_electronica_data">
                <div class="card-header bg-custom-gray text-dark">
                    <h3 class="card-title ">
                        Datos Acceso Facturación Electrónica
                    </h3>
                </div>
                <div class="card-body">


                    <form id="datosFacturacionElectronica">
                        @csrf
                        <div class="row">
                            <div class="col">
                                <!-- input tipo-soap -->
                                <div class="mb-3">
                                    <label for="soap_tipo" class="form-label">Tipo de Entorno</label>
                                    <select class="form-control" id="soap_tipo" name="soap_tipo" required>
                                        <option value="produccion" @if ($empresa->soap_tipo == 'produccion') selected @endif>
                                            Produccion</option>
                                        <option value="demo" @if ($empresa->soap_tipo == 'demo') selected @endif>Demo
                                        </option>
                                        <option value="interno" @if ($empresa->soap_tipo == 'interno') selected @endif>
                                            Interno
                                        </option>
                                    </select>
                                </div>
                                <!-- input soap-avio -->
                                <div class="mb-3">
                                    <label for="soap_envio" class="form-label">Uso de SOAP</label>
                                    <select class="form-control" id="soap_envio" name="soap_envio" required>
                                        <option value="sunat" @if ($empresa->soap_envio == 'sunat') selected @endif>Sunat
                                        </option>
                                        <option value="ose" @if ($empresa->soap_envio == 'ose') selected @endif>OSE
                                        </option>
                                    </select>
                                </div>

                            </div>
                            <div class="col">
                                <!-- input soap-usuario -->
                                <div class="mb-3">
                                    <label for="soap_usuario" class="form-label">Usuario SOAP</label>
                                    <input type="text" class="form-control" id="soap_usuario" name="soap_usuario"
                                        value="{{ $empresa->soap_usuario }}" required>
                                </div>
                                <!-- input soap-clave -->
                                <div class="mb-3">
                                    <label for="soap_clave_usuario" class="form-label">Clave SOAP</label>
                                    <input type="text" class="form-control" id="soap_clave_usuario"
                                        name="soap_clave_usuario" value="{{ $empresa->soap_clave_usuario }}"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-end d-flex">
                            <button type="submit" class="btn btn-xsuccess btn-save"
                                nombre="datosFacturacionElectronica">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card facturacion_electronica_data">
                <div class="card-header bg-custom-gray text-dark">
                    <h3 class="card-title ">
                        Datos del Certificado Digital
                    </h3>
                </div>
                <div class="card-body">


                    <form id="certificadodigital" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-12 col-md-6">
                                <label for="certificado_file" class="form-label">Certificado Digital</label>
                                <input type="file" class="form-control" id="certificado_file"
                                    name="certificado_file" required>
                            </div>
                            <div class="mb-3 col-12 col-md-3">
                                <label for="certificado_pass" class="form-label">Clave Certificado</label>
                                <input type="text" class="form-control" id="certificado_pass"
                                    name="certificado_pass" required>
                            </div>
                            <div class="mb-3 col-12 col-md-3">
                                <label for="certificado_caducidad" class="form-label">Fecha Vencimiento
                                    Certificado</label>
                                <input type="date" class="form-control" id="certificado_caducidad"
                                    name="certificado_caducidad" required>
                            </div>
                        </div>
                        <div class="row justify-content-end d-flex">
                            <button type="submit"
                                class="btn btn-xsuccess btn-save-certificado">Guardar</button>
                        </div>
                    </form>

                </div>
            </div>
            <div class="card facturacion_electronica_data">
                <div class="card-header bg-custom-gray text-dark">
                    <h3 class="card-title ">
                        Consulta documentos Electronicos
                    </h3>
                </div>
                <div class="card-body">


                    <form id="datosConsultaDocumentos">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <!-- input validador_client_id -->
                                <div class="mb-3">
                                    <label for="validador_client_id" class="form-label">Usuario id - client id
                                    </label>
                                    <input type="text" class="form-control" id="validador_client_id"
                                        name="validador_client_id" value="{{ $empresa->validador_client_id }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <!-- input validador_client_secret -->
                                <div class="mb-3">
                                    <label for="validador_client_secret" class="form-label">Clave de usuario - Client
                                        Secret</label>
                                    <input type="text" class="form-control" id="validador_client_secret"
                                        name="validador_client_secret"
                                        value="{{ $empresa->validador_client_secret }}" required>
                                </div>

                            </div>
                        </div>
                        <div class="row justify-content-end d-flex">
                            <button type="submit" class="btn btn-xsuccess btn-save"
                                nombre="datosConsultaDocumentos">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card facturacion_electronica_data">
                <div class="card-header bg-custom-gray text-dark">
                    <h3 class="card-title ">
                        Guia de Remision Electronica
                    </h3>
                </div>
                <div class="card-body">

                    <form id="datosGuiaRemision">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <!-- input validador_client_id -->
                                <div class="mb-3">
                                    <label for="guia_remision_client_id" class="form-label">Usuario id - client id
                                    </label>
                                    <input type="text" class="form-control" id="guia_remision_client_id"
                                        name="guia_remision_client_id" value="{{ $empresa->validador_client_id }}"
                                        required>
                                </div>




                            </div>
                            <div class="col-12 col-md-6">
                                <!-- input validador_client_secret -->
                                <div class="mb-3">
                                    <label for="guia_remision_client_secret" class="form-label">Clave de usuario -
                                        Client
                                        Secret</label>
                                    <input type="text" class="form-control" id="guia_remision_secret"
                                        name="guia_remision_client_secret"
                                        value="{{ $empresa->validador_client_secret }}" required>
                                </div>

                            </div>
                        </div>
                        <div class="row justify-content-end d-flex">


                            <button type="submit" class="btn btn-xsuccess btn-save"
                                nombre="datosGuiaRemision">Guardar</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- </x-pageBody> --}}
</x-admin-layout>
<script>
    $(document).ready(function() {

        mostrar_datos_facturacion_electronica();

        $(document).on('click', '.btn-save', function() {
            let idform = $(this).attr('nombre')
            no_send_form('#' + idform);
            let model = {
                nombre: 'datos ' + idform + ' de la empresa', // se muestra en vista
                route: '/configuracion/empresa/' + idform,
            };
            let data = $('#' + idform).serialize();
            store_data(data, model, idform);
        });

        function store_data(data, model, idform) {
            let form = document.getElementById(idform);
            if (!form.checkValidity()) {
                return;
            }
            showConfirmSwal({
                accion: 'Guardar',
                entidad: model.nombre,
                id: $('#id').val(),
                name: $('#name').val(),
            }).then(confirm => {
                if (confirm == null) {
                    return
                }

                $.ajax({
                    type: "PUT",
                    url: model.route,
                    data: data,
                    success: function(data) {
                        swal_message_response(data);
                        mostrar_datos_facturacion_electronica();
                    },
                    error: function(errors) {
                        show_validate_errors(errors);

                    }
                });
            })

        }

        //guardar datos de certificado digital junto con el archivo
        $(document).on('click', '.btn-save-certificado', function() {
            no_send_form('#certificadodigital');

            let data = new FormData(document.getElementById('certificadodigital'));

            //enviar el certificado en el por los header
            $.ajax({
                type: "POST",
                url: '/configuracion/empresa/certificadodigital',
                data: data,
                contentType: false,
                processData: false,
                success: function(data) {
                    swal_message_response(data);
                },
                error: function(errors) {
                    show_validate_errors(errors);
                }
            });
        });

        function mostrar_datos_facturacion_electronica() {

            if ($('#facturacion_electronica').val() == 'SI') {
                $('.facturacion_electronica_data').show();
            } else {
                $('.facturacion_electronica_data').hide();
            }
        }

    });
</script>
