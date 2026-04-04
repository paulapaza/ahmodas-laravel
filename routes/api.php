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

// Traslados desde Almacén (API libres)
Route::get('/inventario/traslados/datos-gestion', [TrasladoAlmacenController::class, 'getDataGestionApi']);
Route::post('/inventario/traslados/guardar', [TrasladoAlmacenController::class, 'store']);
Route::post('/inventario/traslados/actualizar-stock', [TrasladoAlmacenController::class, 'actualizarStock']);
Route::post('/inventario/traslados/actualizar-venta', [TrasladoAlmacenController::class, 'actualizarVenta']);
Route::post('/inventario/traslados/actualizar-devolucion', [TrasladoAlmacenController::class, 'actualizarDevolucion']);
Route::post('/inventario/traslados/eliminar', [TrasladoAlmacenController::class, 'eliminarTraslado']);
Route::get('/inventario/traslados/historial-datos', [TrasladoAlmacenController::class, 'getHistorial']);

