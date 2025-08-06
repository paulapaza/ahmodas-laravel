// payment-manager.js - Gestor de métodos de pago
class PaymentManager {
    constructor() {
        this.activeInput = null;
        this.newValue = '';
        this.totalCarrito = 0;
        this.bindEvents();
    }

    /**
     * Vincula los eventos de pago
     */
    bindEvents() {
        $('body').on('focus', 'input', (e) => {
            const newInput = e.currentTarget;
            // Si no hay input activo O es un input diferente al anterior, resetear newValue
            if (!this.activeInput || this.activeInput !== newInput) {
                this.resetNewValue(); // USAR EL MÉTODO DEDICADO
            }
            
            this.activeInput = newInput;
           
        });
        // Detectar input activo
        $('body').on('focus', 'input', (e) => {
            this.activeInput = e.currentTarget;
        });

        // Botones del keypad
        $('.keypad-btn').on('click', (e) => {
            this.handleKeypadClick(e.currentTarget);
        });

        // Botón Enter del keypad
        $('.keypad-enter').on('click', () => {
            this.handleKeypadEnter();
        });

        // Blur de inputs del carrito
        $('#table-carrito input').on('blur', (e) => {
            this.handleCartInputBlur(e.currentTarget);
        });

        // Botones de modo de pago
        $('.btn-modo-pago').on('click', (e) => {
            this.handlePaymentModeClick(e.currentTarget);
        });

        // Cambios manuales en inputs de pago
        $('.tipo-pago').on('input', (e) => {
            const id = $(e.currentTarget).attr('id').replace('pago_', '');
            this.handleManualChange(id);
        });

        // Enter en inputs de pago
        $('.tipo-pago').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                this.handlePaymentInputEnter(e.currentTarget);
            }
        });

        // Escuchar cambios en el total del carrito
        $(document).on('cart:totalChanged', (e, total) => {
            this.totalCarrito = total;
            this.distributeToEffective();
        });
        // Detectar input activo
         
    }

    /**
     * Maneja clicks en el keypad
     */
    handleKeypadClick(button) {
        if (!this.activeInput) return;

        const key = $(button).data('key');

        if (key === '←') {
            this.newValue = this.newValue.slice(0, -1);
        } else {
            this.newValue += key;
        }

        this.activeInput.value = this.newValue;
    }
       /**
     * Método adicional: resetear manualmente si es necesario
     */
    resetNewValue() {
        this.newValue = '';
        console.log('newValue reseteado manualmente');
    }
    /**
     * Maneja el botón Enter del keypad
     */
    handleKeypadEnter() {
        if (!this.activeInput) return;

        if ($(this.activeInput).closest('#table-carrito').length) {
            if (this.activeInput.classList.contains('iptCantidad')) {
                this.handleQuantityChange(this.activeInput);
            }else{
                this.handleCartKeypadEnter();
            }
    
        } else if ($(this.activeInput).hasClass('tipo-pago')) {
            this.handlePaymentKeypadEnter();
        }

        this.resetKeypad();
    }
    /**
     * Maneja el cambio de cantidad con el keypad
     */
    handleQuantityChange(input) {
        const table = window.cartManager.table;
        const row = table.row($(input).closest('tr'));
        const data = row.data();
        const nuevaCantidad = parseInt($(input).val(), 10); 

        data.cantidad = nuevaCantidad;
        data.subtotal = POSUtils.formatCurrency(data.cantidad * parseFloat(data.precio_unitario));
        row.data(data).draw();
        window.cartManager.calculateTotal();

    }

    /**
     * Maneja Enter del keypad para inputs del carrito
     */
    /* handleCartKeypadEnter() {
        const table = window.cartManager.table;
        const row = table.row($(this.activeInput).closest('tr'));
        const data = row.data();
        const valorFinal = parseFloat(this.newValue);

        if (isNaN(valorFinal)) return;

        if (valorFinal >= data.precio_minimo) {
            data.precio_unitario = POSUtils.formatCurrency(valorFinal);
            data.subtotal = POSUtils.formatCurrency(data.cantidad * valorFinal);
            row.data(data).draw();
            this.activeInput.value = POSUtils.formatCurrency(valorFinal);
            window.cartManager.calculateTotal();
        } else {
            POSUtils.showError(`El precio mínimo de este producto es: ${data.precio_minimo}`);
            this.newValue = data.precio_unitario.toString();
            this.activeInput.value = this.newValue;
        }
    } */
    handleCartKeypadEnter() {
        const table = window.cartManager.table;
        const row = table.row($(this.activeInput).closest('tr'));
        const data = row.data();
        const valorFinal = parseFloat(this.newValue);

        // Obtener la restricción del usuario (ajusta según cómo accedas a esta variable)
        const restriccion_precio_minimo = window.cartManager.restriccion_precio_minimo;
        // O si la tienes en otro lugar: this.restriccion_precio_minimo

        console.log('Valor ingresado keypad:', valorFinal);
        console.log('Precio mínimo:', data.precio_minimo);
        console.log('Restricción usuario:', restriccion_precio_minimo);

        if (isNaN(valorFinal)) return;

        // Si el usuario tiene restricción de precio mínimo (restriccion_precio_minimo = 'si')
        if (restriccion_precio_minimo === 'si') {
            // Usuario restringido: debe respetar el precio mínimo
            if (valorFinal >= data.precio_minimo) {
                // Precio válido - actualizar
                data.precio_unitario = POSUtils.formatCurrency(valorFinal);
                data.subtotal = POSUtils.formatCurrency(data.cantidad * valorFinal);
                row.data(data).draw();
                this.activeInput.value = POSUtils.formatCurrency(valorFinal);
                window.cartManager.calculateTotal();
            } else {
                // Precio menor al mínimo - mostrar error
                POSUtils.showError(`El precio mínimo de este producto es: ${data.precio_minimo}`);
                this.newValue = data.precio_unitario.toString();
                this.activeInput.value = this.newValue;
            }
        } else {
            // Usuario sin restricción (restriccion_precio_minimo = 'no'): puede poner cualquier precio
            data.precio_unitario = POSUtils.formatCurrency(valorFinal);
            data.subtotal = POSUtils.formatCurrency(data.cantidad * valorFinal);
            row.data(data).draw();
            this.activeInput.value = POSUtils.formatCurrency(valorFinal);
            window.cartManager.calculateTotal();
        }
    }

    /**
     * Maneja Enter del keypad para inputs de pago
     */
    handlePaymentKeypadEnter() {
        const valorFinal = parseFloat(this.newValue);
        if (isNaN(valorFinal)) return;

        this.activeInput.value = POSUtils.formatCurrency(valorFinal);
        const paymentType = this.activeInput.id.replace('pago_', '');
        this.handleManualChange(paymentType);

        if (parseFloat(this.activeInput.value) <= 0) {
            $(this.activeInput).prop('disabled', true);
        }
    }

    /**
     * Maneja blur en inputs del carrito
     */
    handleCartInputBlur(input) {
        if (!this.activeInput) return;

        const table = window.cartManager.table;
        const row = table.row($(input).closest('tr'));
        const data = row.data();
        const valorFinal = parseFloat($(input).val());

        if (valorFinal < data.precio_minimo) {
            POSUtils.showError(`El precio mínimo de este producto es: ${data.precio_minimo}`);
            this.newValue = data.precio_unitario.toString();
            input.value = this.newValue;
        }

        this.resetKeypad();
    }

    /**
     * Resetea el keypad
     */
    resetKeypad() {
        this.activeInput = null;
        this.newValue = '';
    }

    /**
     * Distribuye el total en efectivo
     */
    distributeToEffective() {
        $('#pago_efectivo').val(POSUtils.formatCurrency(this.totalCarrito));
        $('#pago_tarjeta, #pago_yape, #pago_transferencia').val('0.00').prop('disabled', true);
    }

    /**
     * Maneja cambios manuales en métodos de pago
     */
    handleManualChange(target) {
        const valorManual = parseFloat($(`#pago_${target}`).val()) || 0;
        const otros = ['efectivo', 'tarjeta', 'yape', 'transferencia'].filter(x => x !== target);

        let sumaOtros = 0;
        otros.forEach(metodo => {
            if (metodo !== 'efectivo') {
                sumaOtros += parseFloat($(`#pago_${metodo}`).val()) || 0;
            }
        });

        const restante = this.totalCarrito - (valorManual + sumaOtros);
        $('#pago_efectivo').val(POSUtils.formatCurrency(Math.max(0, restante)));
    }

    /**
     * Mueve monto desde efectivo a otro método
     */
    moveFromEffective(targetMethod) {
        const montoEfectivo = parseFloat($('#pago_efectivo').val()) || 0;
        const actualDestino = parseFloat($(`#pago_${targetMethod}`).val()) || 0;

        const nuevoMonto = montoEfectivo + actualDestino;
        $(`#pago_${targetMethod}`).val(POSUtils.formatCurrency(nuevoMonto));
        $('#pago_efectivo').val('0.00');
    }

    /**
     * Maneja click en botones de modo de pago
     */
    handlePaymentModeClick(button) {
        $('.btn-modo-pago').removeClass('active');
        $(button).addClass('active');

        const metodo = $(button).data('target');

        if (metodo === 'efectivo') {
            this.distributeToEffective();
            return;
        }

        if ($('#pago_efectivo').val() <= 0) {
            return;
        }

        this.moveFromEffective(metodo);
        $(`#pago_${metodo}`).focus().prop('disabled', false);
    }

    /**
     * Maneja Enter en inputs de pago
     */
    handlePaymentInputEnter(input) {
        const id = $(input).attr('id').replace('pago_', '');
        $(input).val(POSUtils.formatCurrency($(input).val()));
        this.handleManualChange(id);

        if (parseFloat($(input).val()) <= 0) {
            $(input).prop('disabled', true);
        }
    }

    /**
     * Obtiene los montos de pago
     */
    getPaymentAmounts() {
        return {
            efectivo: parseFloat($('#pago_efectivo').val()) || 0,
            tarjeta: parseFloat($('#pago_tarjeta').val()) || 0,
            yape: parseFloat($('#pago_yape').val()) || 0,
            transferencia: parseFloat($('#pago_transferencia').val()) || 0
        };
    }

    /**
     * Valida los montos de pago
     */
    validatePayments() {
        const amounts = this.getPaymentAmounts();
        const total = amounts.efectivo + amounts.tarjeta + amounts.yape + amounts.transferencia;

        if (total <= 0) {
            POSUtils.showError('Debe ingresar al menos un monto en los métodos de pago');
            return false;
        }

        if (Math.abs(total - this.totalCarrito) > 0.01) { // Tolerancia para decimales
            POSUtils.showError('El total de los métodos de pago debe ser igual al total del carrito');
            return false;
        }

        return true;
    }

    /**
     * Limpia los inputs de pago
     */
    clear() {
        $('#pago_efectivo, #pago_tarjeta, #pago_yape, #pago_transferencia')
            .val('0.00').prop('disabled', true);
        $('.btn-modo-pago').removeClass('active');
        $('.btn-modo-pago[data-target="efectivo"]').addClass('active');
    }
}