<?php

use App\Http\Controllers\Configuracion\UserController;
use App\Http\Controllers\Inventario\ProductoController;
use App\Http\Controllers\Inventario\TiendaController;
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

// pos order
Route::post('/productos/import-excel', [ProductoController::class, 'guardarProductosDesdeExcel'])->name('productos.importExcel');

