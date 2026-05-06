<?php

use App\Http\Controllers\Configuracion\UserController;
use App\Http\Controllers\Inventario\ProductoController;
use App\Http\Controllers\Inventario\TiendaController;
use App\Http\Controllers\Inventario\TrasladoAlmacenController;
use App\Http\Controllers\Pos\PosOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');

Route::post('/inventario/tiendas/cambiar-tienda', [TiendaController::class, 'cambiarTienda']);
Route::post('/punto-de-venta/venta/libre', [PosOrderController::class, 'store'])->name('puntodeventa.venta.store.libre');

Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
  ->name('users.resetPassword');

Route::post('/user/create-user', [UserController::class, 'store'])->name('user.createUser');

use App\Http\Controllers\Pos\PrintController;

Route::get('/print/order/{id}', [PrintController::class, 'getOrderTicket'])->name('print.order');

// Traslados desde Almacén (Gestión Protegida)
Route::middleware('check.cajero.traslados')->group(function () {
    Route::get('/inventario/traslados/datos-gestion', [TrasladoAlmacenController::class, 'getDataGestionApi']);
    Route::post('/inventario/traslados/guardar', [TrasladoAlmacenController::class, 'store']);
    Route::post('/inventario/traslados/actualizar-stock', [TrasladoAlmacenController::class, 'actualizarStock']);
    Route::post('/inventario/traslados/actualizar-venta', [TrasladoAlmacenController::class, 'actualizarVenta']);
    Route::post('/inventario/traslados/actualizar-devolucion', [TrasladoAlmacenController::class, 'actualizarDevolucion']);
    Route::post('/inventario/traslados/eliminar', [TrasladoAlmacenController::class, 'eliminarTraslado']);
    Route::post('/inventario/traslados/importar-excel', [TrasladoAlmacenController::class, 'importarStockExcel']);
    Route::post('/inventario/traslados/importar-excel-chancar', [TrasladoAlmacenController::class, 'importarStockExcelChancar']);

    // Traslados desde Tiendas (Gestión Protegida)
    Route::get('/inventario/traslados-tiendas/datos-gestion', [\App\Http\Controllers\Inventario\TrasladoTiendaController::class, 'getDataGestionApi']);
    Route::get('/inventario/traslados-tiendas/productos/{tiendaId}', [\App\Http\Controllers\Inventario\TrasladoTiendaController::class, 'getProductsByTienda']);
    Route::post('/inventario/traslados-tiendas/guardar', [\App\Http\Controllers\Inventario\TrasladoTiendaController::class, 'store']);
});

// Rutas de Historial (Acceso para todos)
Route::get('/inventario/traslados/historial-datos', [TrasladoAlmacenController::class, 'getHistorial']);
Route::get('/inventario/traslados/historial-global', [TrasladoAlmacenController::class, 'getHistorialGlobal']);

// Historial Tiendas
Route::get('/inventario/traslados-tiendas/historial-global', [\App\Http\Controllers\Inventario\TrasladoTiendaController::class, 'getHistorialGlobal']);

