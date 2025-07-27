
// POS.js - Lógica del Punto de Venta
// Autor: Pedro Herrera

document.addEventListener("DOMContentLoaded", function () {

    // Objeto principal para encapsular lógica POS
    const POS = {
        csrfToken: document.querySelector('input[name="_token"]').value,
        carrito: [],
        total: 0,

        init: function () {
            this.initLarajax();
            this.initCarritoTable();
            this.bindEventosPago();
        },

        // Inicializa la tabla de productos con Larajax
        initLarajax: function () {
            const dataCrud = {
                route: "/inventario/producto",
                subject: "Producto",
                model: "producto",
                csrf: this.csrfToken,
            };

            new Larajax({
                data: dataCrud,
                idTable: "#table-Productos",
                topButton: false,
                columns: [
                    { data: "id" },
                    { data: "codigo_barras" },
                    { data: "nombre" },
                    { data: "precio_unitario" },
                    { data: "precio_minimo", visible: false }
                ]
            });
        },

        // Inicializa la tabla del carrito con DataTables
        initCarritoTable: function () {
            new DataTable('#table-carrito', {
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                lengthChange: false,
                responsive: true,
                language: { emptyTable: '' },
                columns: [
                    { data: 'cantidad', width: '7%' },
                    { data: 'nombre', width: '48%' },
                    {
                        data: 'precio_unitario',
                        className: 'text-right',
                        render: POS.renderPrecioInput
                    },
                    { data: 'precio_minimo', visible: false }
                ]
            });
        },

        // Renderiza el input del precio unitario
        renderPrecioInput: function (data) {
            return `<input type="text" min="1" class="iptPrecio-unitario"
                        style="text-align: center; width:65px; border-radius: 5px; border: 1px solid #ced4da;"
                        value="${data}" />`;
        },

        // Enlaza inputs de métodos de pago para recalcular vuelto
        bindEventosPago: function () {
            ["efectivo", "yape", "transferencia", "tarjeta"].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener("input", POS.recalcularVuelto);
                }
            });
        },

        // Lógica para calcular el vuelto (placeholder)
        recalcularVuelto: function () {
            // Aquí puedes sumar los montos de los inputs y comparar con el total
            let totalPago = 0;
            ["efectivo", "yape", "transferencia", "tarjeta"].forEach(id => {
                const el = document.getElementById(id);
                totalPago += el ? parseFloat(el.value) || 0 : 0;
            });

            const totalVenta = POS.total;
            const vuelto = totalPago - totalVenta;
            const elVuelto = document.getElementById("vuelto");

            if (elVuelto) {
                elVuelto.innerText = vuelto >= 0 ? vuelto.toFixed(2) : "0.00";
            }
        }
    };

    // Ejecutamos
    POS.init();
});
