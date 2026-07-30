<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;


Route::prefix('v1')->group(function () {

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});
