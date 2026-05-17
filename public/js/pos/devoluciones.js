$(document).ready(function() {
    let productosDevueltos = [];
    let productosNuevos = [];
    let _token = $('input[name="_token"]').val();

    function calcularTotales() {
        let totalDevuelto = 0;
        let totalNuevo = 0;

        $('#table-devueltos tbody').empty();
        productosDevueltos.forEach((p, index) => {
            totalDevuelto += p.cantidad * p.precio_unitario;
            $('#table-devueltos tbody').append(`
                <tr>
                    <td>
                        <input type="number" class="form-control form-control-sm devuelto-cant" data-index="${index}" value="${p.cantidad}" min="1" style="width: 60px;">
                    </td>
                    <td>${p.nombre}</td>
                    <td>S/ ${p.precio_unitario.toFixed(2)}</td>
                    <td>S/ ${(p.cantidad * p.precio_unitario).toFixed(2)}</td>
                    <td><button class="btn btn-sm btn-danger btn-remove-devuelto" data-index="${index}"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `);
        });

        $('#table-nuevos tbody').empty();
        productosNuevos.forEach((p, index) => {
            totalNuevo += p.cantidad * p.precio_unitario;
            $('#table-nuevos tbody').append(`
                <tr>
                    <td>
                        <input type="number" class="form-control form-control-sm nuevo-cant" data-index="${index}" value="${p.cantidad}" min="1" style="width: 60px;">
                    </td>
                    <td>${p.nombre}</td>
                    <td>S/ ${p.precio_unitario.toFixed(2)}</td>
                    <td>S/ ${(p.cantidad * p.precio_unitario).toFixed(2)}</td>
                    <td><button class="btn btn-sm btn-danger btn-remove-nuevo" data-index="${index}"><i class="fa-solid fa-trash"></i></button></td>
                </tr>
            `);
        });

        $('#total-devueltos').text(totalDevuelto.toFixed(2));
        $('#total-nuevos').text(totalNuevo.toFixed(2));

        let diferencia = totalNuevo - totalDevuelto;
        let textoDiferencia = $('#texto-diferencia');
        
        if (diferencia > 0) {
            textoDiferencia.html(`Diferencia a COBRAR: <span class="text-success">S/ ${diferencia.toFixed(2)}</span>`);
            $('#metodo_pago_devolucion').val('Efectivo'); // Default
        } else if (diferencia < 0) {
            textoDiferencia.html(`Diferencia a DEVOLVER: <span class="text-danger">S/ ${Math.abs(diferencia).toFixed(2)}</span>`);
            $('#metodo_pago_devolucion').val('Efectivo'); // Default para devoluciones
        } else {
            textoDiferencia.html(`Cambio exacto: <span class="text-secondary">S/ 0.00</span>`);
        }
    }

    function buscarProductoDevolucion(codigo, tipo) {
        if (!codigo) return;
        $.ajax({
            url: "/inventario/producto/buscar",
            type: "POST",
            data: {
                '_token': _token,
                "stringSearch": codigo,
                "tienda_id": window.tiendaIdPos || window.idTiendaUsuario || 1,
                "tipo_busqueda": tipo // Puede ser 'nuevo' o 'devuelto'
            },
            dataType: 'json',
            success: function(respuesta) {
                if (respuesta.length === 1) {
                    let prod = respuesta[0];
                    prod.nombre = prod.alias ? prod.alias : prod.nombre;
                    if (tipo === 'nuevo' && prod.stock_actual <= 0) {
                        Swal.fire({ icon: 'error', text: 'No hay stock disponible de este producto en el almacén.' });
                    } else {
                        agregarProductoListaDevolucion(prod, tipo);
                    }
                } else if (respuesta.length > 1) {
                    let htmlOpciones = `<p class="text-muted small text-center mb-2">${respuesta.length} opciones encontradas</p>`;
                    htmlOpciones += '<div class="list-group text-left" style="max-height: 300px; overflow-y:auto;">';
                    respuesta.forEach(p => {
                        let nombreMostrar = p.alias ? p.alias : p.nombre;
                        let sinStock = (tipo === 'nuevo' && p.stock_actual <= 0);
                        let claseBoton = sinStock ? 'list-group-item-secondary' : 'list-group-item-action btn-seleccionar-producto';
                        let badgeStock = sinStock ? '<span class="badge badge-danger">Sin Stock</span>' : `<span class="badge badge-success">Stock: ${p.stock_actual}</span>`;
                        let opacidad = sinStock ? 'opacity: 0.6;' : '';

                        htmlOpciones += `
                            <button type="button" class="list-group-item ${claseBoton}" style="${opacidad}"
                                data-id="${p.id}" data-nombre="${nombreMostrar}" data-precio="${p.precio_unitario}">
                                <strong>${nombreMostrar}</strong> ${badgeStock}<br>
                                <small>Precio: S/ ${parseFloat(p.precio_unitario).toFixed(2)} | Código: ${p.codigo_barras || '-'}</small>
                            </button>
                        `;
                    });
                    htmlOpciones += '</div>';

                    Swal.fire({
                        title: 'Selecciona un producto',
                        html: htmlOpciones,
                        showConfirmButton: false,
                        showCancelButton: true,
                        cancelButtonText: 'Cerrar',
                        didOpen: () => {
                            $('.btn-seleccionar-producto').click(function() {
                                let productoSeleccionado = {
                                    id: $(this).data('id'),
                                    nombre: $(this).data('nombre'),
                                    precio_unitario: parseFloat($(this).data('precio'))
                                };
                                agregarProductoListaDevolucion(productoSeleccionado, tipo);
                                Swal.close();
                            });
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', text: 'No se encontró el producto' });
                }
            }
        });
    }

    function agregarProductoListaDevolucion(prod, tipo) {
        let producto = {
            id: prod.id,
            nombre: prod.nombre,
            precio_unitario: parseFloat(prod.precio_unitario),
            cantidad: 1
        };

        if (tipo === 'devuelto') {
            let existe = productosDevueltos.find(p => p.id === producto.id);
            if (existe) existe.cantidad++;
            else productosDevueltos.push(producto);
            $('#search-devueltos').val('').focus();
        } else {
            let existe = productosNuevos.find(p => p.id === producto.id);
            if (existe) existe.cantidad++;
            else productosNuevos.push(producto);
            $('#search-nuevos').val('').focus();
        }
        calcularTotales();
    }

    $('#btn-search-devueltos').click(() => buscarProductoDevolucion($('#search-devueltos').val(), 'devuelto'));
    $('#search-devueltos').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarProductoDevolucion($(this).val(), 'devuelto');
        }
    });

    $('#btn-search-nuevos').click(() => buscarProductoDevolucion($('#search-nuevos').val(), 'nuevo'));
    $('#search-nuevos').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarProductoDevolucion($(this).val(), 'nuevo');
        }
    });

    $(document).on('change', '.devuelto-cant', function() {
        let index = $(this).data('index');
        productosDevueltos[index].cantidad = parseInt($(this).val()) || 1;
        calcularTotales();
    });

    $(document).on('change', '.nuevo-cant', function() {
        let index = $(this).data('index');
        productosNuevos[index].cantidad = parseInt($(this).val()) || 1;
        calcularTotales();
    });

    $(document).on('click', '.btn-remove-devuelto', function() {
        let index = $(this).data('index');
        productosDevueltos.splice(index, 1);
        calcularTotales();
    });

    $(document).on('click', '.btn-remove-nuevo', function() {
        let index = $(this).data('index');
        productosNuevos.splice(index, 1);
        calcularTotales();
    });

    // Abrir modal - resetear
    $('#modalDevoluciones').on('show.bs.modal', function () {
        productosDevueltos = [];
        productosNuevos = [];
        $('#motivo_devolucion').val('');
        calcularTotales();
        setTimeout(() => $('#search-devueltos').focus(), 500);
    });

    // Procesar transacción
    $('#btn-procesar-devolucion').click(function() {
        if (productosDevueltos.length === 0) {
            Swal.fire({ icon: 'warning', text: 'Debe ingresar al menos un producto a devolver.' });
            return;
        }

        Swal.fire({
            title: '¿Confirmar Transacción?',
            text: "Se procesará la devolución y/o cambio ajustando los inventarios.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, procesar'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).prop('disabled', true);
                let user_id = $('#userId').val() || 1; // Necesitas asegurar de tener el ID, se puede añadir un input hidden en el blade
                let tienda_id = $('#tiendaId').val() || 1;

                $.ajax({
                    url: '/punto-de-venta/devolucion',
                    type: 'POST',
                    data: {
                        _token: _token,
                        user_id: window.userIdPos || 1, // Usar variable global si existe o obtener de donde corresponda
                        tienda_id: window.tiendaIdPos || 1,
                        productos_devueltos: productosDevueltos,
                        productos_nuevos: productosNuevos,
                        metodo_pago: $('#metodo_pago_devolucion').val(),
                        motivo: $('#motivo_devolucion').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', text: 'Transacción procesada correctamente.' }).then(() => {
                                $('#modalDevoluciones').modal('hide');
                                // Opcional: Recargar página o resetear POS
                            });
                        } else {
                            Swal.fire({ icon: 'error', text: response.message });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', text: 'Error al procesar la petición.' });
                    },
                    complete: () => {
                        $(this).prop('disabled', false);
                    }
                });
            }
        });
    });
});
