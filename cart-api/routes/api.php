<?php
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware('firebase.auth')->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Cart
    Route::get('/cart',             [CartController::class, 'index']);
    Route::post('/cart',            [CartController::class, 'store']);
    Route::put('/cart/{cart}',      [CartController::class, 'update']);
    Route::delete('/cart/{cart}',   [CartController::class, 'destroy']);
    Route::post('/cart/batch',      [CartController::class, 'batch']);
});