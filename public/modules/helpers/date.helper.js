"use strict";

/**
 * Devuelve el rango de fechas desde un año dado (o 2000 si no se pasa)
 * hasta el año actual.
 *
 * @param {number} [year=2000] - Año inicial del rango.
 * @returns {{ start: string, end: string }}
 */
function getYearRange(year = 2000) {
    const currentYear = new Date().getFullYear();
    const y = Number(year);

    if (!Number.isInteger(y)) {
        throw new TypeError('El parámetro "year" debe ser un número entero.');
    }

    if (y > currentYear) {
        throw new RangeError(
            `El año no puede ser mayor que el actual (${currentYear}).`
        );
    }

    return {
        start: `${y}-01-01`,
        end: `${currentYear}-12-31`,
    };
}

function getTodayDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
}
