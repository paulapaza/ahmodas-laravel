// cart-manager.js - Gestor del carrito de compras
class CartManager {
    constructor() {
        this.table = null;
        this.totalCarrito = 0;
        this.initTable();
        this.bindEvents();
        this.restriccion_precio_minimo = window.restriccion_precio_minimo; // Valor por defecto
        console.log('restriccion_precio_minimo: ' + this.restriccion_precio_minimo);
    }

    /**
     * Inicializa la tabla del carrito
     */
    initTable() {
        try {
            // Verificar que la tabla existe
            if (!document.getElementById('table-carrito')) {
                throw new Error('Tabla del carrito no encontrada');
            }

            this.table = new DataTable('#table-carrito', {
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                lengthChange: false,
                responsive: true,
                autoWidth: false,
                language: {
                    emptyTable: 'Carrito vacío'
                },
                columnDefs: [
                    { targets: 3, visible: false }, // precio_minimo oculto
                    { targets: 5, visible: false }, // id oculto
                    { targets: 2, className: 'text-right' },
                    { targets: 4, className: 'text-right' },
                    { targets: 6, className: 'text-center', orderable: false }
                ],
                columns: [
                    {
                        data: 'cantidad',
                        width: '10%',
                        className: 'text-center'
                    },
                    {
                        data: 'nombre',
                        width: '35%'
                    },
                    {
                        data: 'precio_unitario',
                        width: '15%',
                        render: (data, type, row) => {
                            if (type === 'display') {
                                return this.renderPriceInput(data);
                            }
                            return data;
                        }
                    },
                    {
                        data: 'precio_minimo',
                        width: '0%'
                    },
                    {
                        data: 'subtotal',
                        width: '15%',
                        render: (data, type) => {
                            if (type === 'display') {
                                return POSUtils.formatCurrency(data);
                            }
                            return data;
                        }
                    },
                    {
                        data: 'id',
                        width: '0%'
                    },
                    {
                        data: 'boton',
                        width: '25%'
                    }
                ]
            });

            console.log('Tabla del carrito inicializada correctamente');
        } catch (error) {
            console.error('Error al inicializar tabla del carrito:', error);
            POSUtils.showError('Error al inicializar el carrito');
        }
    }

    /**
     * Renderiza el input de precio
     */
    renderPriceInput(data) {
        return `<input type="text" min="1" class="iptPrecio-unitario" 
                    style="text-align: center; width:65px; border-radius: 5px; border: 1px solid #ced4da;"
                    value="${data}" inputmode="none" />`;
    }

    /**
     * Vincula los eventos del carrito
     */
    bindEvents() {
        // Aumentar cantidad
        $(document).on('click', '.aumentar-cantidad', (e) => {
            const id = $(e.currentTarget).data('id');
            this.updateQuantity(id, 1);
        });

        // Disminuir cantidad
        $(document).on('click', '.disminuir-cantidad', (e) => {
            const id = $(e.currentTarget).data('id');
            this.updateQuantity(id, -1);
        });

        // Cambio de precio unitario
        $(document).on('change', '.iptPrecio-unitario', (e) => {

            this.handlePriceChange(e.currentTarget);
        });
    }

    /**
     * Agrega un producto al carrito
     */
    addProduct(id, nombre, precio, precio_minimo) {
        // Verificar si el producto ya existe
        let productExists = false;

        this.table.rows().every(function () {
            const data = this.data();
            if (data.id === id) {
                data.cantidad++;
                data.subtotal = POSUtils.formatCurrency(data.cantidad * parseFloat(data.precio_unitario));
                this.data(data).draw();
                productExists = true;
                return false; // Break loop
            }
        });

        if (productExists) {
            this.calculateTotal();
            return;
        }

        // Agregar nuevo producto
        const productData = {
            cantidad: 1,
            nombre: nombre,
            precio_unitario: precio,
            precio_minimo: precio_minimo,
            subtotal: precio,
            id: id,
            boton: this.generateActionButtons(id)
        };

        this.table.row.add(productData).draw();
        this.calculateTotal();
    }

    /**
     * Genera los botones de acción para cada producto
     */
    generateActionButtons(id) {
        return `
            <button class="btn btn-secondary btn-xs disminuir-cantidad" data-id="${id}">
                <i class="fa-solid fa-minus"></i>
            </button>
            <button class="btn btn-secondary btn-xs aumentar-cantidad" data-id="${id}">
                <i class="fa-solid fa-plus"></i>
            </button>
        `;
    }

    /**
     * Actualiza la cantidad de un producto (cuando se hace con teclado)
     */
    updateQuantity(id, change) {
        const row = this.table.row($(`.aumentar-cantidad[data-id="${id}"], .disminuir-cantidad[data-id="${id}"]`).closest('tr'));
        const data = row.data();

        if (change > 0) {
            data.cantidad++;
        } else {
            if (data.cantidad > 1) {
                data.cantidad--;
            } else {
                row.remove().draw();
                this.calculateTotal();
                return;
            }
        }

        data.subtotal = POSUtils.formatCurrency(data.cantidad * parseFloat(data.precio_unitario));
        row.data(data).draw();
        this.calculateTotal();
    }

    /**
     * Maneja el cambio de precio unitario
     */
    /* handlePriceChange(input) {
        const row = this.table.row($(input).closest('tr'));
        const data = row.data();
        const nuevoPrecio = parseFloat($(input).val());
        const restriccion_precio_minimo = this.restriccion_precio_minimo;
        
        if (!isNaN(nuevoPrecio) && nuevoPrecio >= data.precio_minimo) {
            data.precio_unitario = POSUtils.formatCurrency(nuevoPrecio);
            data.subtotal = POSUtils.formatCurrency(data.cantidad * nuevoPrecio);
            row.data(data).draw();
            this.calculateTotal();
        } else {

            POSUtils.showError(`El precio mínimo de este producto es: ${data.precio_minimo}`);
            $(input).val(data.precio_unitario);
        }
    } */
    handlePriceChange(input) {
        const row = this.table.row($(input).closest('tr'));
        const data = row.data();
        const nuevoPrecio = parseFloat($(input).val());
        const restriccion_precio_minimo = this.restriccion_precio_minimo;
      
        // Verificar si el precio es válido (no NaN)
        if (isNaN(nuevoPrecio)) {
            POSUtils.showError('Por favor ingrese un precio válido');
            $(input).val(data.precio_unitario);
            return;
        }

        // Si el usuario tiene restricción de precio mínimo (restriccion_precio_minimo = 'si')
        if (restriccion_precio_minimo === 'si') {
            // El usuario está restringido: no puede poner precio menor al mínimo
            if (nuevoPrecio < data.precio_minimo) {
                POSUtils.showError(`El precio mínimo de este producto es: ${data.precio_minimo}`);
                $(input).val(data.precio_unitario);
                return;
            }
        }
        // Si restriccion_precio_minimo = 'no', el usuario puede poner cualquier precio

        // Actualizar el precio y recalcular
        data.precio_unitario = POSUtils.formatCurrency(nuevoPrecio);
        data.subtotal = POSUtils.formatCurrency(data.cantidad * nuevoPrecio);
        row.data(data).draw();
        this.calculateTotal();
    }

    /**
     * Calcula el total del carrito
     */
    calculateTotal() {
        let total = 0;
        this.table.rows().every(function () {
            const data = this.data();
            total += parseFloat(data.subtotal);
        });

        this.totalCarrito = total;
        $("#TotalRecibo").text(POSUtils.formatCurrency(total));

        // Notificar cambio de total
        $(document).trigger('cart:totalChanged', [total]);
    }

    /**
     * Obtiene todos los productos del carrito
     */
    getProducts() {
        return this.table.rows().data().toArray().map(producto => ({
            id: producto.id,
            cantidad: producto.cantidad,
            precio_unitario: parseFloat(producto.precio_unitario),
            subtotal: parseFloat(producto.subtotal)
        }));
    }

    /**
     * Limpia el carrito
     */
    clear() {
        this.table.clear().draw();
        this.totalCarrito = 0;
        $("#TotalRecibo").text('0.00');
        $(document).trigger('cart:totalChanged', [0]);
    }

    /**
     * Obtiene el total del carrito
     */
    getTotal() {
        return this.totalCarrito;
    }
}