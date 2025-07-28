// sales-processor.js - Procesador de ventas
class SalesProcessor {
    constructor(cartManager, paymentManager) {
        this.cartManager = cartManager;
        this.paymentManager = paymentManager;
        this.customerService = new CustomerService();
        this.bindEvents();
    }

    /**
     * Vincula los eventos de procesamiento de ventas
     */
    bindEvents() {
        $(document).on('click', '.procesar_venta', (e) => {
            this.handleSaleProcessing(e.currentTarget);
        });

        // Cambio de moneda
        $('#moneda').on('change', (e) => {
            this.handleCurrencyChange(e.currentTarget);
        });
    }

    /**
     * Maneja el procesamiento de ventas
     */
    async handleSaleProcessing(button) {
        const total = this.cartManager.getTotal();

        if (total <= 0) {
            POSUtils.showError('No hay productos agregados al carrito', 'Agrega productos al carrito!');
            return;
        }

        // Deshabilitar botones mientras se procesa
        $('.procesar_venta').prop('disabled', true);

        const codigoTipoComprobante = $(button).attr('codigo_tipo_comprobante');
        let cliente = null;

        try {
            // Obtener datos del cliente si es necesario
            if (codigoTipoComprobante !== POSConfig.DOCUMENT_TYPES.TICKET) {
                cliente = await this.getCustomerData(codigoTipoComprobante);
                if (!cliente) {
                    $('.procesar_venta').prop('disabled', false);
                    return;
                }
            }

            await this.processSale(cliente, codigoTipoComprobante);
        } catch (error) {
            console.error('Error en procesamiento de venta:', error);
            $('.procesar_venta').prop('disabled', false);
        }
    }

    /**
     * Obtiene los datos del cliente según el tipo de comprobante
     */
    async getCustomerData(codigoTipoComprobante) {
        let modalConfig;

        if (codigoTipoComprobante === POSConfig.DOCUMENT_TYPES.BOLETA) {
            modalConfig = CustomerService.getBoletaCustomerData();
        } else if (codigoTipoComprobante === POSConfig.DOCUMENT_TYPES.FACTURA) {
            modalConfig = CustomerService.getFacturaCustomerData();
        }

        const result = await Swal.fire({
            ...modalConfig,
            focusConfirm: false,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'form-modal' },
            willClose: () => {
                $('.procesar_venta').prop('disabled', false);
            }
        });

        return result.isConfirmed ? result.value : null;
    }

    /**
     * Procesa la venta
     */
    async processSale(cliente, codigoTipoComprobante) {
        // Validar pagos
        if (!this.paymentManager.validatePayments()) {
            $('.procesar_venta').prop('disabled', false);
            return;
        }

        const paymentAmounts = this.paymentManager.getPaymentAmounts();
        const productos = this.cartManager.getProducts();

        const saleData = {
            ...paymentAmounts,
            moneda: $('#moneda').val(),
            total: this.cartManager.getTotal(),
            codigo_tipo_comprobante: codigoTipoComprobante,
            cliente: cliente,
            productos: productos,
            tipo_venta: $('#tipo_venta').val()
        };

        try {
            POSUtils.showLoading(
                'Procesando venta...',
                'Por favor, espere un momento. <br> esto puede tardar unos 5 segundos.'
            );

            const response = await POSUtils.makeAjaxRequest(
                POSConfig.ROUTES.PROCESS_SALE,
                saleData
            );

            if (response.success) {
                this.handleSuccessfulSale(response);
                    if (response.pos_order.tipo_comprobante === POSConfig.DOCUMENT_TYPES.TICKET) {
                    window.open(`/pos/imprimir-recibo/${response.pos_order.id}`, '_blank');
                }

            } else {
                POSUtils.showError(response.message);
                $('.procesar_venta').prop('disabled', false);
            }
        } catch (error) {
            console.error('Error al procesar venta:', error);
            POSUtils.showError(
                'Error al procesar el pago: ' + (error.responseJSON?.message || error.message),
                'Intenta nuevamente! presione f5 para recargar la página'
            );
            $('.procesar_venta').prop('disabled', false);
        }
    }

    /**
     * Maneja una venta exitosa
     */
    handleSuccessfulSale(response) {
        let mensaje = response.message;
        let footer = '';

        if (response.pos_order.tipo_comprobante != POSConfig.DOCUMENT_TYPES.TICKET) {
            const nombreComprobante = this.getDocumentTypeName(response.pos_order.tipo_comprobante);

            mensaje = `
                <div class="text-center">
                    <h5>Comprobante: ${nombreComprobante}</h5>
                    <a href="${response.cpe_response.enlace_del_pdf}" target="_blank" class="btn btn-primary">
                        Imprimir Comprobante
                    </a>
                </div>
            `;

            footer = `
                <div class="text-center">
                    <a href="${response.cpe_response.enlace}" target="_blank">
                        ver opciones del Comprobante
                    </a>
                </div>
            `;
        }

        POSUtils.showSuccess(mensaje, footer);
        this.resetPOS();
    }

    /**
     * Obtiene el nombre del tipo de documento
     */
    getDocumentTypeName(tipoComprobante) {
        switch (tipoComprobante) {
            case POSConfig.DOCUMENT_TYPES.FACTURA:
                return 'Factura';
            case POSConfig.DOCUMENT_TYPES.BOLETA:
                return 'Boleta';
            default:
                return 'Ticket';
        }
    }

    /**
     * Resetea el sistema POS después de una venta exitosa
     */
    resetPOS() {
        // Limpiar carrito
        this.cartManager.clear();

        // Limpiar pagos
        this.paymentManager.clear();

        // Reactivar botones
        $('.procesar_venta').prop('disabled', false);

        // Enfocar búsqueda
        $('#search-box').focus();
    }

    /**
     * Maneja el cambio de moneda
     */
    handleCurrencyChange(select) {
        const monedaSeleccionada = $(select).val();
        const simbolo = monedaSeleccionada === POSConfig.CURRENCY.PEN.value
            ? POSConfig.CURRENCY.PEN.symbol
            : POSConfig.CURRENCY.USD.symbol;

        $('#simbolo_moneda').text(simbolo);
    }
}