// pos-config.js - Configuración del sistema POS
class POSConfig {
    static ROUTES = {
        SEARCH_PRODUCT: "/invetario/producto/buscar",
        PROCESS_SALE: "/punto-de-venta/venta",
        CONSULT_DNI: "/consultar-dni",
        CONSULT_RUC: "/consultar-ruc"
    };

    static DOCUMENT_TYPES = {
        BOLETA: "03",
        FACTURA: "01",
        TICKET: "12"
    };

    static CURRENCY = {
        PEN: { value: "1", symbol: "S/ " },
        USD: { value: "2", symbol: "$ " }
    };

    static SALE_TYPES = {
        LOCAL: "local",
        EXPORT: "exportacion"
    };

    static KEYPAD_KEYS = ['7', '8', '9', '4', '5', '6', '1', '2', '3', '0', '.', '←'];

    static VALIDATION = {
        DNI_LENGTH: 8,
        RUC_LENGTH: 11,
        AJAX_TIMEOUT: 7000
    };
}