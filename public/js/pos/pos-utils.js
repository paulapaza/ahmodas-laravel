// pos-utils.js - Utilidades y helpers
class POSUtils {
    /**
     * Valida si un DNI es correcto
     */
    static validateDNI(dni) {
        return /^\d{8}$/.test(dni);
    }

    /**
     * Valida si un RUC es correcto
     */
    static validateRUC(ruc) {
        return /^\d{11}$/.test(ruc);
    }

    /**
     * Formatea un número a 2 decimales
     */
    static formatCurrency(amount) {
        return parseFloat(amount || 0).toFixed(2);
    }

    /**
     * Muestra un mensaje de error con SweetAlert
     */
    static showError(message, footer = 'Intenta nuevamente!') {
        Swal.fire({
            icon: 'error',
            html: message,
            footer: footer
        });
    }

    /**
     * Muestra un mensaje de éxito con SweetAlert
     */
    static showSuccess(message, footer = '') {
        Swal.fire({
            icon: 'success',
            html: message,
            footer: footer,
            showCloseButton: false
        });
    }

    /**
     * Muestra un loading con SweetAlert
     */
    static showLoading(title = 'Procesando...', message = 'Por favor, espere un momento.') {
        Swal.fire({
            title: title,
            html: message,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    /**
     * Muestra validación de error en SweetAlert
     */
    static showValidationError(message) {
        Swal.showValidationMessage(message);
        return false;
    }

    /**
     * Obtiene el CSRF token
     */
    static getCSRFToken() {
        return $('input[name="_token"]').val();
    }

    /**
     * Realiza una petición AJAX con configuración estándar
     */
    static async makeAjaxRequest(url, data, options = {}) {
        const defaultOptions = {
            url: url,
            type: "POST",
            data: { '_token': this.getCSRFToken(), ...data },
            dataType: 'json',
            timeout: POSConfig.VALIDATION.AJAX_TIMEOUT,
            ...options
        };

        return $.ajax(defaultOptions);
    }

    /**
     * Debounce function para optimizar búsquedas
     */
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}