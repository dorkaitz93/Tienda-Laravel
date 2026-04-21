<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

//test
Route::get("/test", function() {
    return "El backend funciona correctamente";
});

//Login & registro:

Route::post("/register", [AuthController::class, 'register']);
Route::post("/login", [AuthController::class, 'login'])->name("login");

// Rutas Protegidas por JWT
Route::middleware("jwt.auth")->group(function(){
    Route::get('/who', [AuthController::class, 'who']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// publico
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

// SOLO admin
Route::middleware(['jwt.auth', 'is_admin'])->group(function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);
});