// product-search.js - Gestor de búsqueda de productos
class ProductSearch {
    constructor(cartManager) {
        this.cartManager = cartManager;
        this.tableProductos = null;
        this.initProductTable();
        this.bindEvents();
    }

    /**
     * Inicializa la tabla de productos
     */
    initProductTable() {
        const dataCrud = {
            route: "/inventario/producto",
            subject: 'Producto',
            model: "producto",
            csrf: POSUtils.getCSRFToken(),
        };
        // Validamos si el usuario tiene el permiso para ver el precio x mayor
        const canSeePrecioMayor = window.currentUserPermissions?.includes('ver-precio-x-mayor');

        const columns = [
            {
                data: 'id',
                visible: false,
            },
            /*   { data: 'codigo_barras' }, */
            { data: 'nombre' },
            { data: 'alias' },
            { data: 'precio_unitario' },
            { data: 'precio_minimo', visible: false }
        ];
        // Si el usuario tiene el permiso, agregamos la columna adicional
        if (canSeePrecioMayor) {
            columns.push({ data: 'precio_x_mayor', title: 'x Mayor' });
        }
        this.tableProductos = new Larajax({
            data: dataCrud,
            idTable: "#table-Productos",
            topButton: false,
            columns: columns,
        });
    }

    /**
     * Vincula los eventos de búsqueda
     */
    bindEvents() {
       
        $("#search-box").on('keydown', (e) => {
            // AL PRESIONAR ENTER O TAB
            if (e.which === 13 || e.which === 9) {
                e.preventDefault();
                this.searchProduct();
            }
        });


        // Click en botón de búsqueda
        $(document).on('click', '#search-button', (e) => {
            e.stopImmediatePropagation();
            this.searchProduct();
        });

        // Toggle container de productos
        $(document).on('click', '#toggle-productos-container', (e) => {
            e.stopImmediatePropagation();
            this.toggleProductsContainer();
        });

        // Click en productos de la tabla
        $("#table-Productos tbody").on("click", "tr", (e) => {
            this.handleProductTableClick(e.currentTarget);
        });

        // F2 para enfocar search box
        $(document).on('keydown', (e) => {
            if (e.key === 'F2') {
                e.preventDefault();
                $('#search-box').focus();
            }
        });
    }

    /**
     * Busca un producto por código de barras
     */
    async searchProduct() {
        const stringSearch = $("#search-box").val().trim();

        if (!stringSearch) {
            POSUtils.showError('Ingrese un código de barras');
            return;
        }

        $("#search-box").focus().val("");

        try {
            const response = await POSUtils.makeAjaxRequest(
                POSConfig.ROUTES.SEARCH_PRODUCT,
                { stringSearch }
            );

            if (response.length > 0) {
                const product = response[0];
                this.cartManager.addProduct(
                    product.id,
                    product.alias,
                    product.precio_unitario,
                    product.precio_minimo
                );
            } else {
                POSUtils.showError('No se encontró el producto');
            }
        } catch (error) {
            console.error('No se Encontro el Producto', error);
            POSUtils.showError('No se encontro el producto con ese código de barras');
        }
    }

    /**
     * Maneja el toggle del container de productos
     */
    toggleProductsContainer() {
        const container = $("#productos-container");
        const button = $("#toggle-productos-container");

        container.toggle();

        if (container.is(":visible")) {
            button.html('<i class="fa-solid fa-minus-square"></i>');
        } else {
            button.html('<i class="fa-solid fa-plus-square"></i>');
        }
    }

    /**
     * Maneja click en la tabla de productos
     */
    handleProductTableClick(row) {
        const data = this.tableProductos.row(row).data();

        if (data) {
            this.cartManager.addProduct(
                data.id,
                data.alias,
                data.precio_unitario,
                data.precio_minimo
            );
        }
    }

    /**
     * Enfoca el campo de búsqueda
     */
    focusSearchBox() {
        $('#search-box').focus();
    }
}