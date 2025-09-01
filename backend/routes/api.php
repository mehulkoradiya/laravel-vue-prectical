<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::get('/stores', [\App\Http\Controllers\StoreController::class, 'index']);

    Route::get('/stocks', [\App\Http\Controllers\StockController::class, 'index']);
    Route::post('/stocks/bulk', [\App\Http\Controllers\StockController::class, 'bulkStore']);
    Route::delete('/stocks/{id}', [\App\Http\Controllers\StockController::class, 'destroy']);
});