// pos-main.js - Archivo principal que inicializa el sistema POS
class POSSystem {
    constructor() {
        this.cartManager = null;
        this.paymentManager = null;
        this.productSearch = null;
        this.salesProcessor = null;
        this.customerService = null;
    }

    /**
     * Inicializa todo el sistema POS
     */
    async init() {
        try {
            console.log("Iniciando sistema POS...");

            // Verificar dependencias
            this.checkDependencies();

            // Inicializar managers en el orden correcto
            await this.initializeComponents();

            // Hacer disponibles globalmente para compatibilidad
            window.cartManager = this.cartManager;
            window.paymentManager = this.paymentManager;

            // Configuración inicial
            this.setupInitialState();

            console.log("✅ Sistema POS inicializado correctamente");
        } catch (error) {
            console.error("❌ Error al inicializar sistema POS:", error);
            this.handleGlobalError(error);
        }
    }

    /**
     * Verifica que todas las dependencias estén disponibles
     */
    checkDependencies() {
        const required = ["jQuery", "DataTable", "Swal"];
        const missing = required.filter(
            (dep) => typeof window[dep] === "undefined"
        );

        if (missing.length > 0) {
            throw new Error(`Dependencias faltantes: ${missing.join(", ")}`);
        }

        // Verificar elementos DOM críticos
        const requiredElements = [
            "#table-carrito",
            "#search-box",
            "#TotalRecibo",
        ];
        const missingElements = requiredElements.filter(
            (el) => !document.querySelector(el)
        );

        if (missingElements.length > 0) {
            throw new Error(
                `Elementos DOM faltantes: ${missingElements.join(", ")}`
            );
        }
    }

    /**
     * Inicializa los componentes del sistema
     */
    async initializeComponents() {
        // Pequeña pausa para asegurar que el DOM esté completamente listo
        await new Promise((resolve) => setTimeout(resolve, 100));

        console.log("Inicializando CartManager...");
        this.cartManager = new CartManager();

        console.log("Inicializando PaymentManager...");
        this.paymentManager = new PaymentManager();

        console.log("Inicializando ProductSearch...");
        this.productSearch = new ProductSearch(this.cartManager);

        console.log("Inicializando SalesProcessor...");
        this.salesProcessor = new SalesProcessor(
            this.cartManager,
            this.paymentManager
        );

        console.log("Inicializando CustomerService...");
        this.customerService = new CustomerService();
    }

    /**
     * Configura el estado inicial del sistema
     */
    setupInitialState() {
        // Enfocar el campo de búsqueda
        $("#search-box").focus();

        // Configurar moneda inicial
        $("#simbolo_moneda").text(POSConfig.CURRENCY.PEN.symbol);

        // Distribuir total inicial en efectivo
        if (this.paymentManager) {
            this.paymentManager.distributeToEffective();
        }

        console.log("Estado inicial configurado");
    }

    /**
     * Maneja errores globales del sistema
     */
    handleGlobalError(error) {
        console.error("Error del sistema POS:", error);

        let message = "Ha ocurrido un error en el sistema";
        let footer = "Por favor, recarga la página o contacta al administrador";

        // Personalizar mensaje según el tipo de error
        if (error.message.includes("Dependencias faltantes")) {
            message = "Faltan librerías necesarias para el sistema";
            footer = "Contacta al administrador del sistema";
        } else if (error.message.includes("Elementos DOM faltantes")) {
            message = "La página no se cargó completamente";
            footer = "Recarga la página e intenta nuevamente";
        }

        POSUtils.showError(message, footer);
    }

    /**
     * Reinicia el sistema en caso de error recuperable
     */
    async restart() {
        console.log("Reiniciando sistema POS...");
        try {
            await this.init();
        } catch (error) {
            console.error("Error al reiniciar:", error);
        }
    }
}

// Inicialización cuando el documento esté listo
$(document).ready(function () {
    // Esperar un poco más para asegurar que todo esté cargado
    setTimeout(async () => {
        try {
            const posSystem = new POSSystem();
            await posSystem.init();

            // Hacer disponible globalmente para debugging
            window.posSystem = posSystem;
        } catch (error) {
            console.error(
                "Error crítico al inicializar el sistema POS:",
                error
            );

            // Mostrar error con opción de reintentar
            Swal.fire({
                icon: "error",
                title: "Error de Inicialización",
                html: "No se pudo inicializar el sistema POS correctamente.",
                footer: "Presiona F5 para recargar la página",
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonText: "Reintentar",
                cancelButtonText: "Recargar Página",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Reintentar inicialización
                    window.location.reload();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location.reload();
                }
            });
        }
    }, 500); // Esperar 500ms para asegurar que todo esté listo
});
