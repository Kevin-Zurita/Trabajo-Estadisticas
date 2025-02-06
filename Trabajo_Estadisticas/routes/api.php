<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AemetController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/clima', [AemetController::class, 'getWeather']);
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando']);
});

