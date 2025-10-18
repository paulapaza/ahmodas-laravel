"use strict";

/**
 * Inicializa una DataTable reutilizable con manejo automático de destrucción.
 * @param {string} selector - Selector CSS del elemento tabla (ej. '#products-table')
 * @param {object} options - Opciones de configuración de DataTable
 * @returns {DataTable} - Instancia creada de DataTable
 */
function initDataTable(selector, options = {}) {
    // Si ya existe una DataTable con ese selector, destruirla antes
    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().clear().destroy();
    }

    // Configuración por defecto (puedes personalizarla)
    const defaultOptions = {
        processing: true,
        serverSide: true,
        pageLength: 10,
        responsive: true,
        columnDefs: [
            { targets: 0, className: "text-left pl-3", width: "80px" },
            { targets: -1, className: "text-right pr-3" },
            { targets: "_all", className: "text-left" },
        ],
        layout: {
            topStart: {
                buttons: [
                    "pageLength",
                    "copy",
                    "excel",
                    "csv",
                    "print",
                    "colvis",
                ]
            }
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            loadingRecords: "</br>",
            processing: "</br>",
        }
    };

    // Mezcla las opciones por defecto con las que pasas tú
    const finalOptions = $.extend(true, {}, defaultOptions, options);

    // Inicializa y retorna la DataTable
    return $(selector).DataTable(finalOptions);
}
