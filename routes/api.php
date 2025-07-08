<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Auth\AuthController;
use \App\Http\Controllers\StoreController;
use \App\Http\Controllers\ProductController;


// public routes 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

// Protected routes 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Store routes
    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
    Route::post('/store', [StoreController::class, 'store'])->name('store.store');
    Route::get('/store/{store}', [StoreController::class, 'show'])->name('store.show');
    Route::put('/store/{store}', [StoreController::class, 'update'])->name('store.update');
    Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');

    // Product routes
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
    Route::post('/product/{product}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
