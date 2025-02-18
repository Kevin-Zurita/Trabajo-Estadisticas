<?php

use App\Http\Controllers\procesaStat;
use Illuminate\Http\Request;
use App\Http\Controllers\RecolectaDatosController;
use App\Http\Controllers\RecolectaInventario;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/estaciones', [RecolectaInventario::class, 'getAllStations']);
Route::get('/datos', [RecolectaInventario::class, 'recolectaDatos']);
Route::get('procesar', [RecolectaInventario::class, 'procesaStat']);
Route::get('/almacenar', [RecolectaInventario::class, 'procesaYalmacenaStat']);