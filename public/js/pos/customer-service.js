// customer-service.js - Servicio para manejo de clientes
class CustomerService {
    constructor() {
        this.bindEvents();
    }

    /**
     * Vincula los eventos de búsqueda de clientes
     */
    bindEvents() {
        // Búsqueda de DNI
        $(document).on('click', '#buscar_dni', () => {
            this.searchDNI();
        });

        // Búsqueda de RUC
        $(document).on('click', '.fa-magnifying-glass', (e) => {
            // Solo si está en el contexto de RUC
            if ($('#ruc_cliente').length) {
                this.searchRUC();
            }
        });
    }

    /**
     * Busca información por DNI
     */
    async searchDNI() {
        const dni = $('#dni_cliente').val();
        
        if (!POSUtils.validateDNI(dni)) {
            $('#resultado_busqueda').html(
                '<span class="text-danger">El DNI debe tener exactamente 8 dígitos numéricos</span>'
            );
            return;
        }

        try {
            $('#resultado_busqueda').html(
                '<span class="text-info">Buscando DNI...</span><i class="fa fa-spinner fa-spin"></i>'
            );

            const response = await $.ajax({
                url: `${POSConfig.ROUTES.CONSULT_DNI}/${dni}`,
                type: "GET",
                timeout: POSConfig.VALIDATION.AJAX_TIMEOUT,
                dataType: 'json'
            });

            if (response.success) {
                $('#nombre_cliente').val(response.data.full_name || '');
                $('#direccion_cliente').val('S/D');
                $('#resultado_busqueda').html(
                    '<span class="text-success">DNI encontrado.</span>'
                );
            } else {
                this.handleSearchError('DNI no encontrado: ' + response.message);
                $('#razon_social_cliente').val('');
            }
        } catch (error) {
            this.handleAjaxError(error, 'DNI');
        }
    }

    /**
     * Maneja errores de búsqueda
     */
    handleSearchError(message) {
        $('#resultado_busqueda').html(
            `<span class="text-danger">${message}</span>`
        );
    }

    /**
     * Maneja errores de AJAX
     */
    handleAjaxError(error, type) {
        if (error.statusText === 'timeout') {
            this.handleSearchError('Tiempo de espera agotado. Intente nuevamente.');
        } else if (error.status === 404) {
            this.handleSearchError(`${type} no encontrado.`);
            if (type === 'DNI') {
                $('#nombre_cliente').val('');
            } else {
                $('#razon_social_cliente').val('');
            }
        } else {
            this.handleSearchError(`Error al consultar ${type}. Intente nuevamente.`);
        }
    }

    /**
     * Obtiene datos del cliente para boleta
     */
    static getBoletaCustomerData() {
        return {
            title: 'Datos cliente para la boleta',
            html: `
                <div class="form-contenedor">
                    <div class="dni-contenedor">
                        <input type="text" id="dni_cliente" class="form-input" placeholder="DNI">
                        <i class="fa-solid fa-magnifying-glass" data-toggle="tooltip" data-placement="top" title="buscar datos" id="buscar_dni"></i>
                    </div>
                    <span id="resultado_busqueda"></span>
                    <input type="text" id="nombre_cliente" class="form-input" placeholder="Nombre">
                    <input type="text" id="direccion_cliente" class="form-input" placeholder="Dirección">
                </div>
            `,
            preConfirm: () => {
                const dni = Swal.getPopup().querySelector('#dni_cliente').value;
                const nombre = Swal.getPopup().querySelector('#nombre_cliente').value;
                const direccion = Swal.getPopup().querySelector('#direccion_cliente').value;

                if (dni && !POSUtils.validateDNI(dni)) {
                    return POSUtils.showValidationError('El DNI debe tener exactamente 8 dígitos numéricos');
                }

                if (dni && !nombre) {
                    return POSUtils.showValidationError('Falta Nombre');
                }

                return { dni, nombre, direccion, tipo_documento: '1' };
            }
        };
    }

    /**
     * Obtiene datos del cliente para factura
     */
    static getFacturaCustomerData() {
        return {
            title: 'Datos cliente para la factura',
            html: `
                <div class="form-contenedor">
                    <div class="ruc-contenedor">
                        <input type="text" id="ruc_cliente" class="form-input" placeholder="RUC">
                        <i class="fa-solid fa-magnifying-glass" data-toggle="tooltip" data-placement="top" title="buscar RUC"></i>
                    </div>
                    <span id="resultado_busqueda"></span>
                    <input type="text" id="razon_social_cliente" class="form-input" placeholder="Razón Social">
                    <input type="text" id="direccion_cliente" class="form-input" placeholder="Dirección">
                </div>
            `,
            preConfirm: () => {
                const ruc = Swal.getPopup().querySelector('#ruc_cliente').value;
                const razonSocial = Swal.getPopup().querySelector('#razon_social_cliente').value;
                const direccion = Swal.getPopup().querySelector('#direccion_cliente').value;

                if (!ruc || !razonSocial || !direccion) {
                    return POSUtils.showValidationError('Por favor, completa todos los campos');
                }

                if (!POSUtils.validateRUC(ruc)) {
                    return POSUtils.showValidationError('El RUC debe tener exactamente 11 dígitos numéricos');
                }

                return { 
                    ruc, 
                    razonSocial, 
                    direccion, 
                    tipo_documento: '6' 
                };
            }
        };
    }


    /**
     * Busca información por RUC
     */
    async searchRUC() {
        const ruc = $('#ruc_cliente').val();
        
        if (!POSUtils.validateRUC(ruc)) {
            $('#resultado_busqueda').html(
                '<span class="text-danger">El RUC debe tener exactamente 11 dígitos numéricos</span>'
            );
            return;
        }

        try {
            $('#resultado_busqueda').html(
                '<span class="text-info">Buscando RUC...</span><i class="fa fa-spinner fa-spin"></i>'
            );

            const response = await $.ajax({
                url: `${POSConfig.ROUTES.CONSULT_RUC}/${ruc}`,
                type: "GET",
                timeout: POSConfig.VALIDATION.AJAX_TIMEOUT,
                dataType: 'json'
            });

            if (response.success) {
                $('#razon_social_cliente').val(response.data.razon_social || '');
                $('#direccion_cliente').val(
                    `${response.data.direccion} - ${response.data.distrito} - ${response.data.provincia} - ${response.data.departamento}`
                );
                $('#resultado_busqueda').html(
                    '<span class="text-success">RUC encontrado:</span>'
                );
            } else {
                $('#resultado_busqueda').html(
                    '<span class="text-danger">RUC no encontrado</span>'
                );
            }
        } catch (error) {
            console.error('Error al buscar RUC:', error);
            $('#resultado_busqueda').html(
                '<span class="text-danger">Error al buscar RUC</span>'
            );
        }
    }
    }