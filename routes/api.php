<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

//test
Route::get("/test", function() {
    return "El backend funciona correctamente";
});

//Login & registro:

Route::post("register", [AuthController::class, 'register']);

Route::post("login", [AuthController::class, 'login'])->middleware("throttle:login")->name("login");

// Rutas Protegidas por JWT
Route::middleware("jwt.auth")->group(function(){
    Route::get('who', [AuthController::class, 'who']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
});

// publico
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

// SOLO admin
Route::middleware(['jwt.auth', 'is_admin'])->group(function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
    Route::get('admin/all-orders', [OrderController::class, 'allOrders']);
    Route::put('admin/orders/{order}/status', [OrderController::class, 'updateStatus']);

});