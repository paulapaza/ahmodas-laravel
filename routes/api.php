<?php

use App\Http\Controllers\Pos\PosOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/punto-de-venta/venta/libre', [PosOrderController::class, 'store'])->name('puntodeventa.venta.store.libre');
