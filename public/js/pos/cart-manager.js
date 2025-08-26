// cart-manager.js - Gestor del carrito de compras
class CartManager {
    constructor() {
        this.table = null;
        this.totalCarrito = 0;
        this.initTable();
        this.bindEvents();
        this.restriccion_precio_minimo = window.restriccion_precio_minimo; // Valor por defecto
        // disminucion y aumento de precio por defecto 1 sol
        this.precio_aumento = 1;
        this.precio_disminucion = -1;

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
                    { targets: 5, visible: false }, // precio_minimo oculto
                    { targets: 7, visible: false }, // id oculto
                    { targets: 2, className: 'text-right' },
                    { targets: [2,4], className: 'boton-cambiar-precio' },
                    { targets: 3, className: 'text-center boton-cambiar-precio' },
                    { targets: 6, className: 'text-right', orderable: false }
                ],
                columns: [
                    {
                        data: 'cantidad',
                        width: '10%',
                        className: 'text-center',
                        render: (data, type) => {
                            if (type === 'display') {
                                return this.renderQuantityInput(data);
                            }
                            return data;
                        }
                    },
                    {
                        data: 'nombre',
                        width: '50%'
                    },
                    {
                        data: 'botonmenos',
                        width: '10%'
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
                        data: 'botonmas',
                        width: '10%'
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
     * Renderiza el input de la cantidad
     */
    renderQuantityInput(data) {
        return `<input type="number" min="0" class="iptCantidad" 
                    style="text-align: center; width:35px; border-radius: 5px; border: 1px solid #ced4da;"
                    value="${data}" inputmode="none" />`;
    }

    /**
     * Vincula los eventos del carrito
     */
    bindEvents() {
        // Aumentar cantidad
        $(document).on('click', '.aumentar-cantidad', (e) => {
            const id = $(e.currentTarget).data('id');
            this.updatePrecioUnitario(id, this.precio_aumento);
        });

        // Disminuir cantidad
        $(document).on('click', '.disminuir-cantidad', (e) => {
            const id = $(e.currentTarget).data('id');
            this.updatePrecioUnitario(id, this.precio_disminucion);
        });

        // Cambio de precio unitario
        $(document).on('change', '.iptPrecio-unitario', (e) => {

            this.handlePriceChange(e.currentTarget);
        });
        // Cambio de cantidad
        $(document).on('change', '.iptCantidad', (e) => {
            this.handleQuantityChange(e.currentTarget);
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
            botonmenos: this.generateActionButtonsmenos(id),
            precio_unitario: precio,
            botonmas: this.generateActionButtonsmas(id),
            precio_minimo: precio_minimo,
            subtotal: precio,
            id: id,
        };

        this.table.row.add(productData).draw();
        this.calculateTotal();
    }

    /**
     * Genera los botones de acción aumentar precio para cada producto
     */
    generateActionButtonsmenos(id) {
        return `
            
            <button class="btn bg-white btn-xs disminuir-cantidad" data-id="${id}">
                <i class="fa-solid fa-circle-minus"></i>
            </button>
        `;
    }
    generateActionButtonsmas(id) {
        return `
            
            <button class="btn bg-white btn-xs aumentar-cantidad" data-id="${id}">
                <i class="fa-solid fa-circle-plus"></i>
            </button>
        `;
    }


    /**
     * Actualiza el precio  de un producto (cuando con los botones + / - son presionados)
     */
    updatePrecioUnitario(id, change) {
        const row = this.table.row($(`.aumentar-cantidad[data-id="${id}"], .disminuir-cantidad[data-id="${id}"]`).closest('tr'));
        const data = row.data();

        if (change > 0) {
            //revisar  si tiene restricción de precio mínimo

            data.precio_unitario++;
            //formatear a 2 decimales
            data.precio_unitario = POSUtils.formatCurrency(data.precio_unitario);
        } else {
            //revisar  si el usuario tiene restricción de precio mínimo y comprobar que el precio no sea menor al este precio
            const precioUnitario = parseFloat(data.precio_unitario);
            const precioMinimo = parseFloat(data.precio_minimo);
            if (this.restriccion_precio_minimo === 'si' && precioUnitario <= precioMinimo) {
                POSUtils.showError(`El precio mínimo de este producto es: ${precioMinimo}`);
                return;
            }
            if (data.precio_unitario <= 1) {
                POSUtils.showError('El precio no puede ser menor a 1');
                return;
            }
            data.precio_unitario--;
            //formatear a 2 decimales
            data.precio_unitario = POSUtils.formatCurrency(data.precio_unitario);
            if (data.precio_unitario < 1) data.precio_unitario = 1; // No permitir precio menor a 1
        }

        data.subtotal = POSUtils.formatCurrency(data.cantidad * parseFloat(data.precio_unitario));
        row.data(data).draw();
        this.calculateTotal();
    }

    /**
     * Maneja el cambio de precio unitario desde el input
     */
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
     * Maneja el cambio de cantidad desde el input
     */

    handleQuantityChange(input) {
        const row = this.table.row($(input).closest('tr'));
        const data = row.data();
        const nuevaCantidad = parseInt($(input).val(), 10);
        const restriccion_precio_minimo = this.restriccion_precio_minimo;
        // Verificar si la cantidad es válida (no NaN y mayor que 0)
        if (isNaN(nuevaCantidad)) {
            POSUtils.showError('Por favor ingrese una cantidad válida');
            $(input).val(data.cantidad);
            return;
        }
        if (nuevaCantidad < 1) {
            //eliminar producto si la cantidad es menor a 1
            this.table.row(row).remove().draw();
            this.calculateTotal();
            return;
        }

        // Actualizar la cantidad y recalcular
        data.cantidad = nuevaCantidad;
        data.subtotal = POSUtils.formatCurrency(data.cantidad * parseFloat(data.precio_unitario));
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