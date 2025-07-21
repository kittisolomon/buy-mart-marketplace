<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Auth\AuthController;
use \App\Http\Controllers\StoreController;
use \App\Http\Controllers\ProductController;
use \App\Http\Controllers\CartController;


// public routes 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

// Protected routes 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Store routes
    Route::prefix('store')->name('store.')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::post('/', [StoreController::class, 'store'])->name('store');
        Route::get('/{store}', [StoreController::class, 'show'])->name('show');
        Route::put('/{store}', [StoreController::class, 'update'])->name('update');
        Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy');
    });

    // Product routes
    Route::prefix('product')->name('product.')->group( function() {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Cart routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/{product}', [CartController::class, 'store'])->name('store');
        Route::put('/item/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/item/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    });
    
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


