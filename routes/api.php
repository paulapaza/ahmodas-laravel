<?php

use App\Http\Controllers\Inventario\TiendaController;
use App\Http\Controllers\Pos\PosOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/inventario/tiendas/cambiar-tienda', [TiendaController::class, 'cambiarTienda']);
Route::post('/punto-de-venta/venta/libre', [PosOrderController::class, 'store'])->name('puntodeventa.venta.store.libre');
