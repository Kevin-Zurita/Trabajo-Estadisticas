<?php

use Illuminate\Http\Request;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\almacenaStat;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/estaciones', [WeatherController::class, 'getAllStations']);
Route::get('/estacion/{idema}', [WeatherController::class, 'getWeatherData']);
